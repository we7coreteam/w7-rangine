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

namespace W7\Core\Database;

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
use W7\Core\Database\Connection\PdoMysqlConnection;
use W7\Core\Database\Connection\SwooleMySqlConnection;
use W7\Core\Dispatcher\EventDispatcher;
use W7\Core\Provider\ProviderAbstract;

class DatabaseProvider extends ProviderAbstract {
	public function register() {
		$this->registerDb();
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
			if (App::getApp()->getContext()->getContextDataByKey('db-transaction')) {
				return false;
			}
			return $this->releaseDb($data, $container);
		});
		$dbDispatch->listen(TransactionBeginning::class, function ($data) {
			$connection = $data->connection;
			App::getApp()->getContext()->setContextDataByKey('db-transaction', $connection);
		});
		$dbDispatch->listen(TransactionCommitted::class, function ($data) use ($container) {
			if (idb()->transactionLevel() === 0) {
				App::getApp()->getContext()->setContextDataByKey('db-transaction', null);
				return $this->releaseDb($data, $container);
			}
		});
		$dbDispatch->listen(TransactionRolledBack::class, function ($data) use ($container) {
			if (idb()->transactionLevel() === 0) {
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

	private function releaseDb($data, $container) {
		$connection = $data->connection;
		ilogger()->channel('database')->debug(($data->sql ?? '') . ', params: ' . implode(',', (array) (empty($data->bindings) ? [] : $data->bindings)));

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
}
