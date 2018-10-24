<?php
/**
 * @author donknap
 * @date 18-10-23 上午11:48
 */

namespace W7\Core\Database\Pool;


use Illuminate\Database\Connectors\MySqlConnector;
use W7\Core\Pool\CoPoolAbstract;

class Pool extends CoPoolAbstract {
	/**
	 * 用于创建连接对象
	 * @var MySqlConnector
	 */
	private $creator;
	private $config;

	public function setCreator(MySqlConnector $creator) {
		$this->creator = $creator;
	}

	public function createConnection() {
		if (empty($this->creator)) {
			throw new \RuntimeException('Invalid db creator');
		}
		$connection = $this->creator->connect($this->config);
		return $connection;
	}

	/**
	 * @param mixed $config
	 */
	public function setConfig($config) {
		$this->config = $config;
	}
}