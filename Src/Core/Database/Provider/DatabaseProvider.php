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
use Illuminate\Contracts\Container\BindingResolutionException;
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
use W7\Core\Provider\ProviderAbstract;

class DatabaseProvider extends ProviderAbstract {
	/**
	 * @throws BindingResolutionException
	 * @throws \Exception
	 */
	public function register(): void {
		$this->registerConnectionResolver();
		$this->registerDbEvent();

		Model::setEventDispatcher($this->getEventDispatcher());
		Model::setConnectionResolver($this->container->get('db-factory'));
	}

	private function registerConnectionResolver(): void {
		$this->container->set('db-factory', function () {
			Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
				return new PdoMysqlConnection($connection, $database, $prefix, $config);
			});

			$container = Container::getInstance();
			$container['config']['database.default'] = 'default';
			$container['config']['database.connections'] = $this->config->get('app.database', []);
			$factory = new ConnectionFactory($container);

			$connectionResolver = new ConnectionResolver($container, $factory);
			$connectionResolver->setPoolFactory(new PoolFactory($this->config->get('app.pool.database', [])));
			$container['db'] = $connectionResolver;
			Facade::setFacadeApplication($container);

			return $connectionResolver;
		});
	}

	/**
	 * @throws \Exception
	 */
	private function registerDbEvent(): void {
		$this->getEventDispatcher()->listen(QueryExecuted::class, function ($event) {
			$this->getEventDispatcher()->dispatch(new QueryExecutedEvent($event->sql, $event->bindings, $event->time, $event->connection));
		});
		$this->getEventDispatcher()->listen(TransactionBeginning::class, function ($event) {
			$this->getEventDispatcher()->dispatch(new TransactionBeginningEvent($event->connection));
		});
		$this->getEventDispatcher()->listen(TransactionCommitted::class, function ($event) {
			$this->getEventDispatcher()->dispatch(new TransactionCommittedEvent($event->connection));
		});
		$this->getEventDispatcher()->listen(TransactionRolledBack::class, function ($event) {
			$this->getEventDispatcher()->dispatch(new TransactionRolledBackEvent($event->connection));
		});
	}
}
