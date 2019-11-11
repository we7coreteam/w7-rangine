<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Database\Provider;

use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Fluent;
use W7\App;
use W7\Console\Application;
use W7\Core\Database\Connection\PdoMysqlConnection;
use W7\Core\Database\Connection\SwooleMySqlConnection;
use W7\Core\Database\ConnectorManager;
use W7\Core\Database\DatabaseManager;
use W7\Core\Dispatcher\EventDispatcher;
use W7\Core\Log\LogManager;
use W7\Core\Provider\ProviderAbstract;

class DatabaseProvider extends ProviderAbstract {
	public function register() {
		$this->registerOpenBaseDir(BASE_PATH . '/database');
		$this->registerLog();
		$this->registerDb();

		$application = iloader()->get(Application::class);
		$application->autoRegisterCommands($this->rootPath . '/Command', 'W7\Core\Database');
	}

	/**
	 * model -> newQuery -> DatabaseMananger -> function connection ->
	 *      Factory -> createConnector 拿到一个Pdo连接 （ConnectorManager -> 从连接池里拿一个Pdo连接） -> createConnection 放置Pdo连接，生成连接操作对象 (PdoMysqlConnection)
	 *
	 * @return bool
	 */
	private function registerDb() {
		//新增swoole连接mysql的方式
		Connection::resolverFor('swoolemysql', function ($connection, $database, $prefix, $config) {
			return new SwooleMySqlConnection($connection, $database, $prefix, $config);
		});
		Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
			return new PdoMysqlConnection($connection, $database, $prefix, $config);
		});

		$container = new Container();
		$container->instance('db.connector.swoolemysql', new ConnectorManager());
		$container->instance('db.connector.mysql', new ConnectorManager());

		//侦听sql执行完后的事件，回收$connection
		/**
		 * @var EventDispatcher $dbDispatch
		 */
		$dbDispatch = iloader()->get(EventDispatcher::class);
		$dbDispatch->setContainer($container);

		$dbDispatch->listen(QueryExecuted::class, function ($data) use ($container) {
			/**
			 *检测是否是事物里面的query
			 */
			$this->log($data);
			if (App::getApp()->getContext()->getContextDataByKey('db-transaction')) {
				return false;
			}
			return $this->releaseDb($data, $container);
		});
		$dbDispatch->listen(TransactionBeginning::class, function ($data) {
			$connection = $data->connection;
			$data->sql = 'begin transaction';
			$this->log($data);
			App::getApp()->getContext()->setContextDataByKey('db-transaction', $connection);
		});
		$dbDispatch->listen(TransactionCommitted::class, function ($data) use ($container) {
			if (idb()->transactionLevel() === 0) {
				$data->sql = 'commit transaction';
				$this->log($data);
				App::getApp()->getContext()->setContextDataByKey('db-transaction', null);
				return $this->releaseDb($data, $container);
			}
		});
		$dbDispatch->listen(TransactionRolledBack::class, function ($data) use ($container) {
			if (idb()->transactionLevel() === 0) {
				$data->sql = 'rollback transaction';
				$this->log($data);
				App::getApp()->getContext()->setContextDataByKey('db-transaction', null);
				return $this->releaseDb($data, $container);
			}
		});

		$container->instance('events', $dbDispatch);

		//添加配置信息到容器
		$dbconfig = \iconfig()->getUserAppConfig('database');

		$container->instance('config', new Fluent());
		$container['config']['database.default'] = 'default';
		$container['config']['database.connections'] = $dbconfig;
		$factory = new ConnectionFactory($container);
		$dbManager = new DatabaseManager($container, $factory);

		Model::setEventDispatcher($dbDispatch);
		Model::setConnectionResolver($dbManager);
	}

	private function log($data) {
		$sql = $data->sql ?? '';
		$bindings = (array) (empty($data->bindings) ? [] : $data->bindings);
		foreach ($bindings as $key => $binding) {
			// This regex matches placeholders only, not the question marks,
			// nested in quotes, while we iterate through the bindings
			// and substitute placeholders by suitable values.
			$regex = is_numeric($key)
				? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/"
				: "/:{$key}(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";

			// Mimic bindValue and only quote non-integer and non-float data types
			if (!is_int($binding) && !is_float($binding)) {
				$binding = $data->connection->getPdo()->quote($binding);
			}

			$sql = preg_replace($regex, $binding, $sql, 1);
		}
		ilogger()->channel('database')->debug('connection ' . $data->connectionName . ', ' . $sql);
	}

	private function releaseDb($data, $container) {
		$connection = $data->connection;

		$poolName = $connection->getPoolName();
		if (empty($poolName)) {
			return true;
		}
		list($poolType, $poolName) = explode(':', $poolName);
		if (empty($poolType)) {
			$poolType = 'swoolemysql';
		}

		$activePdo = $connection->getActiveConnection();
		if (empty($activePdo)) {
			return false;
		}
		$connectorManager = $container->make('db.connector.' . $poolType);
		$pool = $connectorManager->getCreatedPool($poolName);
		if (empty($pool)) {
			return true;
		}
		$pool->releaseConnection($activePdo);
	}

	private function registerLog() {
		if (!empty($this->config->getUserConfig('log')['channel']['database'])) {
			return false;
		}
		/**
		 * @var LogManager $logManager
		 */
		$logManager = iloader()->get(LogManager::class);
		$logManager->addChannel('database', 'stream', [
			'path' => RUNTIME_PATH . '/logs/db.log',
			'level' => ((ENV & DEBUG) === DEBUG) ? 'debug' : 'info'
		]);
	}
}
