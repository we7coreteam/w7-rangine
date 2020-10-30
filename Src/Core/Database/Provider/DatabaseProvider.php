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
use Illuminate\Support\Facades\Facade;
use W7\Core\Database\Connection\PdoMysqlConnection;
use W7\Core\Database\ConnectionResolver;
use W7\Core\Database\Event\QueryExecutedEvent;
use W7\Core\Database\Event\TransactionBeginningEvent;
use W7\Core\Database\Event\TransactionCommittedEvent;
use W7\Core\Database\Event\TransactionRolledBackEvent;
use W7\Core\Database\Pool\PoolFactory;
use W7\Core\Facades\Config;
use W7\Core\Facades\Event;
use W7\Core\Provider\ProviderAbstract;

class DatabaseProvider extends ProviderAbstract {
	public function register() {
		$this->registerConnectionResolver();
		$this->registerDbEvent();

		Model::setEventDispatcher(Event::getFacadeRoot());
		Model::setConnectionResolver($this->container->get(ConnectionResolver::class));
	}

	private function registerConnectionResolver() {
		$this->container->set(ConnectionResolver::class, function () {
			Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
				return new PdoMysqlConnection($connection, $database, $prefix, $config);
			});

			/**
			 * @var Container $container
			 */
			$container = $this->container->get(Container::class);

			$container['config']['database.default'] = 'default';
			$container['config']['database.connections'] = $this->config->get('app.database', []);
			$factory = new ConnectionFactory($container);

			$connectionResolver = new ConnectionResolver($container, $factory);
			$connectionResolver->setPoolFactory(new PoolFactory(Config::get('app.pool.database', [])));
			$container['db'] = $connectionResolver;
			Facade::setFacadeApplication($container);

			return $connectionResolver;
		});
	}

	private function registerDbEvent() {
		Event::listen(QueryExecuted::class, function ($event) {
			Event::dispatch(new QueryExecutedEvent($event->sql, $event->bindings, $event->time, $event->connection));
		});
		Event::listen(TransactionBeginning::class, function ($event) {
			Event::dispatch(new TransactionBeginningEvent($event->connection));
		});
		Event::listen(TransactionCommitted::class, function ($event) {
			Event::dispatch(new TransactionCommittedEvent($event->connection));
		});
		Event::listen(TransactionRolledBack::class, function ($event) {
			Event::dispatch(new TransactionRolledBackEvent($event->connection));
		});
	}
}
