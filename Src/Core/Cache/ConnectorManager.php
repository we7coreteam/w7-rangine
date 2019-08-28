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
use W7\Core\Cache\Handler\HandlerAbstract;
use W7\Core\Cache\Pool\Pool;

class ConnectorManager {
	private $config;
	private $pool;

	public function __construct() {
		$this->pool = [];
		$this->config['connection'] = \iconfig()->getUserAppConfig('cache') ?? [];
		$this->config['pool'] = \iconfig()->getUserAppConfig('pool')['cache'] ?? [];
	}

	public function connect($name = 'default') : CacheInterface {
		$config = $this->config['connection'][$name] ?? [];
		$poolConfig = $this->config['pool'][$name] ?? [];

		if (empty($config)) {
			throw new \RuntimeException('Cache is not configured.');
		}

		if (empty($poolConfig) || empty($poolConfig['enable'])) {
			ilogger()->channel('cache')->debug($name . ' create connection without pool');
			/**
			 * @var HandlerAbstract $handlerClass
			 */
			$handlerClass = $this->checkHandler($config['driver']);
			return $handlerClass::getHandler($config);
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
		$pool->setCreator($this->checkHandler($config['driver']));
		$pool->setMaxCount($poolConfig['max'] ?? 1);

		$this->pool[$name] = $pool;
		return $this->pool[$name];
	}

	private function checkHandler($handler) {
		$className = sprintf('\\W7\\Core\\Cache\\Handler\\%sHandler', ucfirst($handler));
		if (!class_exists($className)) {
			//处理自定义的handler
			$className = sprintf('\\W7\\App\\Handler\\Cache\\%sHandler', ucfirst($handler));
		}
		if (!class_exists($className)) {
			throw new \RuntimeException('cache handler ' . $handler . ' is not supported');
		}

		$reflectClass = new \ReflectionClass($className);
		if (!in_array(CacheInterface::class, array_keys($reflectClass->getInterfaces()))) {
			throw new \RuntimeException('please implements Psr\SimpleCache\CacheInterface');
		}

		return $className;
	}
}
