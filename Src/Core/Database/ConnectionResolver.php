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

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use W7\Core\Facades\Context;

class ConnectionResolver extends DatabaseManager {
	public function connection($name = null) {
		list($database, $type) = $this->parseConnectionName($name);
		$name = $name ?: $database;

		$name = $this->getContextKey($name);
		$connection = Context::getContextDataByKey($name);

		if (! $connection instanceof ConnectionInterface) {
			try {
				$connection = $this->configure(
					$this->makeConnection($database),
					$type
				);
				Context::setContextDataByKey($name, $connection);
			} finally {
				if ($connection && isCo()) {
					defer(function () use ($connection, $name) {
						$this->releaseConnection($connection);
						Context::setContextDataByKey($name, null);
					});
				}
			}
		}

		return $connection;
	}

	public function beginTransaction($name = null) {
		return $this->connection($name)->beginTransaction();
	}

	private function releaseConnection($connection) {
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
		$connectorManager = $this->app->make('db.connector.' . $poolType);
		$pool = $connectorManager->getCreatedPool($poolName);
		if (empty($pool)) {
			return true;
		}
		$pool->releaseConnection($activePdo);
	}

	private function getContextKey($name): string {
		return sprintf('database.connection.%s', $name);
	}
}
