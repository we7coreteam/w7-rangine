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

namespace W7\Core\Cache;

use Illuminate\Contracts\Container\BindingResolutionException;
use W7\Core\Cache\Event\AfterMakeConnectionEvent;
use W7\Core\Cache\Event\BeforeMakeConnectionEvent;
use W7\Core\Cache\Handler\HandlerAbstract;
use W7\Core\Cache\Pool\PoolFactory;
use W7\Core\Helper\Traiter\AppCommonTrait;

class ConnectionResolver {
	use AppCommonTrait;

	protected array $connectionConfig = [];
	protected PoolFactory $poolFactory;

	public function __construct(array $connectionConfig = []) {
		$this->connectionConfig = $connectionConfig;
	}

	public function setPoolFactory(PoolFactory $poolFactory): void {
		$this->poolFactory = $poolFactory;
	}

	public function createConnection($name, $usePool = true) {
		if ($usePool && isCo() && $this->poolFactory && !empty($this->poolFactory->getPoolConfig($name)['enable'])) {
			$connection = $this->poolFactory->getPool($name)->getConnection();
			$connection->poolName = $this->poolFactory->getPool($name)->getPoolName();
			return $connection;
		}

		if (empty($this->connectionConfig[$name])) {
			throw new \RuntimeException('cache channel ' . $name . ' not support');
		}

		$connection = $this->connectionConfig[$name]['driver'];
		/**
		 * @var HandlerAbstract $connection
		 */
		return $connection::connect($this->connectionConfig[$name]);
	}

	public function connection($name) {
		$contextCacheName = $this->getContextKey($name);
		$connection = $this->getContext()->getContextDataByKey($contextCacheName);

		if (! $connection instanceof HandlerAbstract) {
			try {
				$this->getEventDispatcher()->dispatch(new BeforeMakeConnectionEvent($name));
				$connection = $this->createConnection($name);
				$this->getEventDispatcher()->dispatch(new AfterMakeConnectionEvent($name, $connection));
				$this->getContext()->setContextDataByKey($contextCacheName, $connection);
			} finally {
				if ($connection && isCo()) {
					$this->getContext()->defer(function () use ($connection, $contextCacheName) {
						$this->releaseConnection($connection);
						$this->getContext()->setContextDataByKey($contextCacheName, null);
					});
				}
			}
		}

		return $connection;
	}

	/**
	 * @throws \ReflectionException
	 * @throws BindingResolutionException
	 */
	public function reconnect($name) {
		$contextCacheName = $this->getContextKey($name);
		/**
		 * @var HandlerAbstract $connection
		 */
		$connection = $this->getContext()->getContextDataByKey($contextCacheName);
		if (!$connection) {
			return $this->connection($name);
		}

		$fresh = $this->createConnection($name, false);
		return $connection->setStorage($fresh->getStorage());
	}

	public function releaseConnection($connection) {
		if (empty($connection->poolName)) {
			return true;
		}

		$pool = $this->poolFactory->getCreatedPool($connection->poolName);
		if (empty($pool)) {
			return true;
		}
		$pool->releaseConnection($connection);
	}

	private function getContextKey($name): string {
		return sprintf('cache.connection.%s', $name);
	}
}
