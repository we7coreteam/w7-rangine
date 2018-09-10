<?php
/**
 * @author donknap
 * @date 18-8-1 下午5:21
 */

namespace W7\Core\Database\Connector;

use Illuminate\Database\Connectors\MySqlConnector;
use W7\Core\Database\Pool\MasterPool;
use W7\Core\Database\Pool\SlavePool;

class SwooleMySqlConnector extends MySqlConnector {
	public $pool;
	private $host;

	public function __construct() {
		$config = \iconfig()->getUserAppConfig('database');
		if (empty($config['default']['write'])) {
			$this->host = [];
		} else {
			$this->host = [
				'master' => $config['default']['write']['host'],
				'slave' => $config['default']['read']['host'],
			];
		}
	}

	public function connect(array $config) {
		//非work进程时不开启连接池，数据不共享
		if (!isCo()) {
			$this->pool = null;
			return parent::connect($config);
		}

		if (empty($config['pool']['enable'])) {
			//不加连接池
			$pool = $this->getMasterPool();
			return $pool->createConnection($config);
		}

		if (empty($this->host) || $config['host'] === $this->host['master'] || in_array($config['host'], $this->host['master'])) {
			$this->pool = $this->getMasterPool();
		} else {
			$this->pool = $this->getSlavePool();
		}

		$this->pool->setMaxActive($config['pool']['max']);

		return $this->pool->getConnection($config);
	}

	private function getMasterPool() {
		return \iloader()->singleton(MasterPool::class);
	}

	private function getSlavePool() {
		return \iloader()->singleton(SlavePool::class);
	}
}
