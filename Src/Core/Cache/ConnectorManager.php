<?php
/**
 * 缓存连接管理
 * @author donknap
 * @date 18-12-30 上午11:59
 */

namespace W7\Core\Cache;

use W7\App;
use W7\Core\Cache\Connection\ConnectionAbstract;
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
		iloader()->set(Pool::class, function () use ($poolName) {
			return new Pool($poolName);
		}, $poolName);
		$pool = iloader()->get(Pool::class);

		$pool->releaseConnection($connection);
	}

	public function connect($name = 'default') {
		$config = $this->config['connection'][$name] ?? [];
		$poolConfig = $this->config['pool'][$name] ?? [];

		if (empty($config)) {
			throw new \RuntimeException('Cache is not configured.');
		}

		$connectionClass = $this->checkDriverSupport($config['driver']);
		/**
		 * @var ConnectionAbstract $connection
		 */
		$connector = App::getApp()->getContext()->getContextDataByKey($connectionClass);
		if ($connector) {
			return $connector->getHandle();
		}

		$connector = new $connectionClass();
		//在非协程情况下，走默认的pool，保持的连接数为1
		if (!isCo()) {
			$poolConfig = [
				'enable' => true,
				'max' => 1
			];
		}
		//协程环境下未启动pool,使用完成后断开连接
		if (empty($poolConfig['enable'])) {
			$connection = $connector->noRelease()->connect($config);
		} else {
			iloader()->set(Pool::class, function () use ($name) {
				return new Pool($name);
			}, $name);
			$pool = iloader()->get(Pool::class);
			$pool->setConfig($config);
			$pool->setMaxCount($poolConfig['max']);
			$pool->setCreator($connector);

			$connection = $pool->getConnection();
			$connector->setHandle($connection);
		}

		//注册到当前上下文中，当上下文销毁的时候触发连接的回收
		App::getApp()->getContext()->setContextDataByKey($connectionClass, $connector);

		return $connection;
	}

	private function checkDriverSupport($driver) {
		$className = sprintf("\\W7\\Core\\Cache\\Connection\\%sConnection", ucfirst($driver));
		if (!class_exists($className)) {
			throw new \RuntimeException('This cache driver is not supported');
		}
		return $className;
	}
}