<?php
/**
 * 缓存连接管理
 * @author donknap
 * @date 18-12-30 上午11:59
 */

namespace W7\Core\Cache;

use Psr\SimpleCache\CacheInterface;
use W7\Core\Cache\Pool\Pool;

class ConnectorManager {
	private $config;
	private $pool;
	
	public function __construct() {
		$this->pool = [];
		$this->config['connection'] = \iconfig()->getUserAppConfig('cache') ?? [];
		$this->config['pool'] = \iconfig()->getUserAppConfig('pool')['cache'] ?? [];
	}

	public function release($connection) {
		if (empty($connection->poolName)) {
			return true;
		}
		list($poolType, $poolName) = explode(':', $connection->poolName);
		/**
		 * @var Pool $pool
		 */
		$pool = iloader()->withClass(Pool::class)
			->withSingle()->withAlias($poolName)
			->withParams(['name' => $poolName])
			->get();

		$pool->releaseConnection($connection);
	}

	public function connect($name = 'default') : CacheInterface {
		$config = $this->config['connection'][$name] ?? [];
		$poolConfig = $this->config['pool'][$name] ?? [];

		if (empty($config)) {
			throw new \RuntimeException('Cache is not configured.');
		}

		$handlerClass = $this->checkHandler($config['driver']);

		if (empty($poolConfig) || empty($poolConfig['enable'])) {
			ilogger()->channel('cache')->debug('create connection without pool');
			return $handlerClass::getHandler($config);
		}

		/**
		 * @var Pool $pool
		 */
		$pool = iloader()->withClass(Pool::class)
					->withSingle()->withAlias($name)
					->withParams(['name' => $name])
					->get();
		$pool->setConfig($config);
		$pool->setMaxCount($poolConfig['max']);
		$pool->setCreator($handlerClass);

		return $pool->getConnection();
	}

	private function checkHandler($handler) {
		$className = sprintf("\\W7\\Core\\Cache\\Handler\\%sHandler", ucfirst($handler));
		if (!class_exists($className)) {
			//处理自定义的handler
			$className = sprintf("\\W7\\App\\Handler\\Cache\\%sHandler", ucfirst($handler));
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