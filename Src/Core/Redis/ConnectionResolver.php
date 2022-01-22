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

namespace W7\Core\Redis;

use Closure;
use Illuminate\Redis\Connections\Connection;
use W7\Contract\Redis\RedisFactoryInterface;
use W7\Core\Redis\Connectors\PhpRedisConnector;
use Illuminate\Redis\Connectors\PredisConnector;
use Illuminate\Redis\RedisManager;
use InvalidArgumentException;
use W7\Core\Redis\Event\AfterMakeConnectionEvent;
use W7\Core\Redis\Event\BeforeMakeConnectionEvent;
use W7\Core\Redis\Pool\PoolFactory;
use W7\Core\Helper\Traiter\AppCommonTrait;

class ConnectionResolver extends RedisManager implements RedisFactoryInterface {
	use AppCommonTrait;

	/**
	 * @var PoolFactory
	 */
	protected $poolFactory;

	public function setPoolFactory(PoolFactory $poolFactory) {
		$this->poolFactory = $poolFactory;
	}

	public function createConnection($name, $usePool = true) {
		if ($usePool && isCo() && $this->poolFactory && !empty($this->poolFactory->getPoolConfig($name)['enable'])) {
			$connection = $this->poolFactory->getPool($name)->getConnection();
			$connection->poolName = $this->poolFactory->getPool($name)->getPoolName();
			return $connection;
		}

		if (empty($this->config[$name])) {
			throw new \RuntimeException('redis channel ' . $name . ' not support');
		}

		return $this->configure(
			$this->resolve($name),
			$name
		);
	}

	public function channel($name = 'default'): Connection {
		return $this->connection($name);
	}

	public function connection($name = null) {
		$name = $name ?: 'default';

		$contextRedisName = $this->getContextKey($name);
		$connection = $this->getContext()->getContextDataByKey($contextRedisName);

		if (! $connection instanceof Connection) {
			try {
				$this->getEventDispatcher()->dispatch(new BeforeMakeConnectionEvent($name));
				$connection = $this->createConnection($name);
				$this->getEventDispatcher()->dispatch(new AfterMakeConnectionEvent($name, $connection));
				$this->getContext()->setContextDataByKey($contextRedisName, $connection);
			} finally {
				if ($connection && isCo()) {
					$this->getContext()->defer(function () use ($connection, $contextRedisName) {
						$this->releaseConnection($connection);
						$this->getContext()->setContextDataByKey($contextRedisName, null);
					});
				}
			}
		}

		return $connection;
	}

	public function resolve($name = null) {
		$name = $name ?: 'default';

		$options = $this->config['options'] ?? [];

		if (isset($this->config[$name])) {
			return $this->connector($this->config[$name]['client'] ?? '')->connect(
				$this->parseConnectionConfiguration($this->config[$name]),
				$options
			);
		}

		if (isset($this->config['clusters'][$name])) {
			return $this->resolveCluster($name);
		}

		throw new InvalidArgumentException("Redis connection [{$name}] not configured.");
	}

	protected function resolveCluster($name) {
		return $this->connector($this->config['clusters'][$name]['client'] ?? '')->connectToCluster(
			array_map(function ($config) {
				return $this->parseConnectionConfiguration($config);
			}, $this->config['clusters'][$name]),
			$this->config['clusters']['options'] ?? [],
			$this->config['options'] ?? []
		);
	}

	protected function connector($driver = '') {
		$driver = $driver ?: $this->driver;
		$customCreator = $this->customCreators[$driver] ?? null;

		if ($customCreator) {
			return $customCreator();
		}

		switch ($driver) {
			case 'predis':
				return new PredisConnector;
			case 'phpredis':
				return new PhpRedisConnector;
		}
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
		return sprintf('redis.connection.%s', $name);
	}

	public function createSubscription($channels, Closure $callback, $method = 'subscribe') {
		// TODO: Implement createSubscription() method.
	}
}
