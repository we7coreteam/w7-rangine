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

use Psr\SimpleCache\CacheInterface;
use W7\Core\Cache\Event\MakeConnectionEvent;
use W7\Core\Cache\Handler\HandlerAbstract;
use W7\Core\Cache\Pool\Pool;
use W7\Core\Helper\Traiter\HandlerTrait;

class ConnectorManager {
	use HandlerTrait;

	private $config;
	private $pool;

	public function __construct() {
		$this->pool = [];
		$this->config['connection'] = \iconfig()->get('app.cache', []);
		$this->config['pool'] = \iconfig()->get('app.pool.cache', []);
	}

	public function connect($name = 'default') : CacheInterface {
		$config = $this->config['connection'][$name] ?? [];
		$poolConfig = $this->config['pool'][$name] ?? [];

		if (empty($config)) {
			throw new \RuntimeException('Cache is not configured.');
		}

		if (!isCo() || empty($poolConfig) || empty($poolConfig['enable'])) {
			/**
			 * @var HandlerAbstract $handlerClass
			 */
			$handlerClass = $this->getHandlerClass($config['driver']);
			$handler = $handlerClass::getHandler($config);

			ievent(new MakeConnectionEvent($name, $handler));

			return $handler;
		}

		return $this->getPool($name)->getConnection();
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
	 * @return mixed
	 */
	private function getPool($name) : Pool {
		if (!empty($this->pool[$name])) {
			return $this->pool[$name];
		}

		$config = $this->config['connection'][$name];
		$poolConfig = $this->config['pool'][$name];

		$pool = new Pool($name);
		$pool->setConfig($config);
		$pool->setCreator($this->getHandlerClass($config['driver']));
		$pool->setMaxCount($poolConfig['max'] ?? 1);

		$this->pool[$name] = $pool;
		return $this->pool[$name];
	}

	private function getHandlerClass($handler) {
		$handlerClass = $this->getHandlerClassByType('cache', $handler);

		$reflectClass = new \ReflectionClass($handlerClass);
		if (!in_array(CacheInterface::class, array_keys($reflectClass->getInterfaces()))) {
			throw new \RuntimeException('please implements Psr\SimpleCache\CacheInterface');
		}

		return $handlerClass;
	}
}
