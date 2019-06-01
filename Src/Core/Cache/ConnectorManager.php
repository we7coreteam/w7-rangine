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
	private $poolConfig;
	private $config;
	private $pool;
	
	public function __construct() {
		$this->pool = [];
		$this->config['connection'] = \iconfig()->getUserAppConfig('cache') ?? [];
		$this->poolConfig = \iconfig()->getUserAppConfig('pool')['cache'] ?? [];
	}

	public function connect($name = 'default') {
		$config = $this->config['connection'][$name] ?? [];

		if (empty($config)) {
			throw new \RuntimeException('Cache is not configured.');
		}

		//未在协程中则不启用连接池
		if (!isCo() || empty($this->poolConfig['enable'])) {
			return $this->getConnectorManager($config)->connect($config);
		}

		return $this->getPool($name)->getConnection();
	}

	public function release($connection) {
		if (empty($connection->poolName)) {
			return true;
		}
		list($poolType, $poolName) = explode(':', $connection->poolName);

		$this->getPool($poolName)->releaseConnection($connection);
	}

	/**
	 * @param $name
	 * @return Pool
	 */
	private function getPool($name, $config) {
		if (!empty($this->pool[$name])) {
			return $this->pool[$name];
		}
		$pool = new Pool($name);
		$pool->setConfig($config);
		$pool->setCreator($this->getConnectorManager($config));
		$pool->setMaxCount($this->poolConfig[$name]['max']);

		$this->pool[$name] = $pool;
		return $this->pool[$name];
	}

	private function checkDriverSupport($driver) {
		$className = sprintf("\\W7\\Core\\Cache\\Connection\\%sConnection", ucfirst($driver));
		if (!class_exists($className)) {
			throw new \RuntimeException('This cache driver is not supported');
		}
		return $className;
	}

	private function getConnectorManager($config) {
		$connectionClass = $this->checkDriverSupport($config['driver']);
		/**
		 * @var ConnectionAbstract $connection
		 */
		$connection = iloader()->get($connectionClass);
		return $connection;
	}
}