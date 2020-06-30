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
use Illuminate\Database\Connectors\MySqlConnector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Fluent;
use W7\Core\Database\Connection\PdoMysqlConnection;
use W7\Core\Database\ConnectorManager;
use W7\Core\Database\DatabaseManager;
use W7\Core\Database\Event\QueryExecutedEvent;
use W7\Core\Database\Event\TransactionBeginningEvent;
use W7\Core\Database\Event\TransactionCommittedEvent;
use W7\Core\Database\Event\TransactionRolledBackEvent;
use W7\Core\Facades\Config;
use W7\Core\Facades\Context;
use W7\Core\Facades\DB;
use W7\Core\Facades\Event;
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
		Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
			return new PdoMysqlConnection($connection, $database, $prefix, $config);
		});

		$container = new Container();
		$connectorManager = new ConnectorManager(Config::get('app.pool.database', []));
		$connectorManager->setEventDispatcher(Event::getFacadeRoot());
		$container->instance('db.connector.mysql', $connectorManager);
		ConnectorManager::registerConnector('mysql', MySqlConnector::class);

		//侦听sql执行完后的事件，回收$connection
		Event::setContainer($container);

		Event::listen(QueryExecuted::class, function ($event) use ($container) {
			/**
			 * @var QueryExecuted $event
			 */
			Event::dispatch(new QueryExecutedEvent($event->sql, $event->bindings, $event->time, $event->connection));
			/**
			 *检测是否是事物里面的query
			 */
			if (Context::getContextDataByKey('db-transaction')) {
				return false;
			}
			return $this->releaseDb($event, $container);
		});
		Event::listen(TransactionBeginning::class, function ($event) {
			/**
			 * @var TransactionBeginning $event
			 */
			Event::dispatch(new TransactionBeginningEvent($event->connection));

			Context::setContextDataByKey('db-transaction', $event->connection);
		});
		Event::listen(TransactionCommitted::class, function ($event) use ($container) {
			if (DB::transactionLevel() === 0) {
				/**
				 * @var TransactionCommitted $event
				 */
				Event::dispatch(new TransactionCommittedEvent($event->connection));

				Context::setContextDataByKey('db-transaction', null);
				return $this->releaseDb($event, $container);
			}
		});
		Event::listen(TransactionRolledBack::class, function ($event) use ($container) {
			if (DB::transactionLevel() === 0) {
				/**
				 * @var TransactionRolledBack $event
				 */
				Event::dispatch(new TransactionRolledBackEvent($event->connection));

				Context::setContextDataByKey('db-transaction', null);
				return $this->releaseDb($event, $container);
			}
		});

		$container->instance('events', Event::getFacadeRoot());
		$container->instance('config', new Fluent());
		$container['config']['database.default'] = 'default';
		$container['config']['database.connections'] = $this->config->get('app.database', []);
		$factory = new ConnectionFactory($container);
		$dbManager = new DatabaseManager($container, $factory);

		Model::setEventDispatcher(Event::getFacadeRoot());
		Model::setConnectionResolver($dbManager);

		$container['db'] = DB::getFacadeRoot();
		Facade::setFacadeApplication($container);
	}

	private function releaseDb($data, $container) {
		$connection = $data->connection;

		$poolName = $connection->getPoolName();
		if (empty($poolName)) {
			return true;
		}
		list($poolType, $poolName) = explode(':', $poolName);
		if (empty($poolType)) {
			$poolType = 'mysql';
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
