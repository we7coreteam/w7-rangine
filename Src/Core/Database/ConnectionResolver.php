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

use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use W7\Core\Database\Event\AfterMakeConnectionEvent;
use W7\Core\Database\Event\BeforeMakeConnectionEvent;
use W7\Core\Database\Pool\PoolFactory;
use W7\Core\Helper\Traiter\AppCommonTrait;

class ConnectionResolver extends DatabaseManager {
	use AppCommonTrait;

	/**
	 * @var PoolFactory
	 */
	protected $poolFactory;

	public function setPoolFactory(PoolFactory $poolFactory) {
		$this->poolFactory = $poolFactory;
	}

	public function createConnection($name = null, $usePool = true) {
		[$database, $type] = $this->parseConnectionName($name);
		$name = $name ?: $database;

		if ($usePool && isCo() && $this->poolFactory && !empty($this->poolFactory->getPoolConfig($name)['enable'])) {
			$connection = $this->poolFactory->getPool($name)->getConnection();
			$connection->poolName = $this->poolFactory->getPool($name)->getPoolName();
			return $connection;
		}

		return $this->configure(
			$this->makeConnection($database),
			$type
		);
	}

	public function connection($name = null) {
		$database = $this->parseConnectionName($name)[0];
		$name = $name ?: $database;

		$contextDbName = $this->getContextKey($name);
		$connection = $this->getContext()->getContextDataByKey($contextDbName);

		if (! $connection instanceof ConnectionInterface) {
			try {
				$this->app['events'] && $this->app['events']->dispatch(new BeforeMakeConnectionEvent($name));
				$connection = $this->createConnection($name);
				$this->app['events'] && $this->app['events']->dispatch(new AfterMakeConnectionEvent($name, $connection));
				$this->getContext()->setContextDataByKey($contextDbName, $connection);
			} finally {
				if ($connection && isCo()) {
					$this->getContext()->defer(function () use ($connection, $contextDbName) {
						$this->releaseConnection($connection);
						$this->getContext()->setContextDataByKey($contextDbName, null);
					});
				}
			}
		}

		return $connection;
	}

	public function disconnect($name = null) {
		/**
		 * @var Connection $connection
		 */
		$connection = $this->getConnectionByNameFromContext($name);
		if ($connection) {
			$connection->disconnect();
		}
	}

	/**
	 * Reconnect to the given database.
	 *
	 * @param  string|null  $name
	 * @return \Illuminate\Database\Connection
	 */
	public function reconnect($name = null) {
		$this->disconnect($name = $name ?: $this->getDefaultConnection());

		if (!$this->getConnectionByNameFromContext($name)) {
			return $this->connection($name);
		}

		return $this->refreshPdoConnections($name);
	}

	/**
	 * Refresh the PDO connections on a given connection.
	 *
	 * @param  string  $name
	 * @return \Illuminate\Database\Connection
	 */
	protected function refreshPdoConnections($name) {
		$fresh = $this->makeConnection($name);

		/**
		 * @var Connection $connection
		 */
		$connection = $this->getConnectionByNameFromContext($name);
		return $connection->setPdo($fresh->getRawPdo())
			->setReadPdo($fresh->getRawReadPdo());
	}

	public function beginTransaction($name = null) {
		$this->connection($name)->beginTransaction();
	}

	private function releaseConnection($connection) {
		if (empty($connection->poolName)) {
			return true;
		}

		$pool = $this->poolFactory->getCreatedPool($connection->poolName);
		if (empty($pool)) {
			return true;
		}
		$pool->releaseConnection($connection);
	}

	private function getConnectionByNameFromContext($name = null) {
		$database = $this->parseConnectionName($name)[0];
		$contextDbName = $name ?: $database;
		$contextDbName = $this->getContextKey($contextDbName);
		return $this->getContext()->getContextDataByKey($contextDbName);
	}

	private function getContextKey($name): string {
		return sprintf('database.connection.%s', $name);
	}
}
