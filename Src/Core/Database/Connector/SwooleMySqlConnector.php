<?php
/**
 * @author donknap
 * @date 18-8-1 下午5:21
 */

namespace W7\Core\Database\Connector;

use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;
use W7\Core\Database\Pool\MasterPool;
use W7\Core\Database\Pool\SlavePool;

class SwooleMySqlConnector extends Connector implements ConnectorInterface
{
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
		//不加连接池
		$this->pool = $this->getMasterPool();
		return $this->pool->createConnection($config);

		//检查传入的HOST是不是为读库
		if (!empty($this->host) && ($config['host'] === $this->host['slave'] || in_array($config['host'], $this->host['slave']))) {
			$this->pool = $this->getSlavePool();
		} else {
			$this->pool = $this->getMasterPool();
		}
		return $this->pool->getConnection($config);
	}

	private function getMasterPool() {
		return \iloader()->singleton(MasterPool::class);
	}

	private function getSlavePool() {
		return \iloader()->singleton(SlavePool::class);
	}
}
