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

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;
use W7\Core\Cache\Event\MakeConnectionEvent;
use W7\Core\Cache\Handler\HandlerAbstract;
use W7\Core\Cache\Pool\Pool;

class ConnectorManager {
	protected $poolConfig;
	protected $pools;
	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;

	public function __construct($poolConfig = []) {
		$this->pools = [];
		$this->poolConfig = $poolConfig;
	}

	public function setEventDispatcher(EventDispatcherInterface $eventDispatcher) {
		$this->eventDispatcher = $eventDispatcher;
	}

	public function connect(array $config) : CacheInterface {
		$poolConfig = $this->poolConfig[$config['name']] ?? [];

		if (empty($config)) {
			throw new \RuntimeException('Cache is not configured.');
		}

		if (!isCo() || empty($poolConfig) || empty($poolConfig['enable'])) {
			return $this->getDefaultConnection($config);
		}

		return $this->getPool($config['name'], $config)->getConnection();
	}

	public function release($connection) {
		if (empty($connection->poolName)) {
			return true;
		}
		list($poolType, $poolName) = explode(':', $connection->poolName);

		return $this->getPool($poolName)->releaseConnection($connection);
	}

	/**
	 * @param $name
	 * @param array $config
	 * @return Pool
	 */
	private function getPool($name, $config = []) : Pool {
		if (!empty($this->pools[$name])) {
			return $this->pools[$name];
		}

		$pool = new Pool($name);
		$pool->setConfig($config);
		$pool->setCreator($config['driver']);
		$pool->setMaxCount($this->poolConfig[$name]['max'] ?? 1);

		$this->pools[$name] = $pool;
		return $this->pools[$name];
	}

	private function getDefaultConnection($config) {
		$handler = $config['driver'];
		/**
		 * @var HandlerAbstract $handler
		 */
		$handler = $handler::getHandler($config);

		$this->eventDispatcher && $this->eventDispatcher->dispatch(new MakeConnectionEvent($config['name'], $handler));

		return $handler;
	}
}
