<?php
/**
 * @author donknap
 * @date 18-7-30 下午6:18
 */

namespace W7\Core\Database\Pool;

use W7\Core\Database\Driver\MySqlCoroutine;
use W7\Core\Pool\PoolAbstract;

class MasterPool extends PoolAbstract {

	public function createConnection($config) {
		$connection = new MySqlCoroutine();
		$connection->connect([
			'host' => $config['host'],
			'port' => !empty($config['port']) ? $config['port'] : '3306',
			'user' => $config['username'],
			'password' => $config['password'],
			'database' => $config['database'],
			'charset' => $config['charset'],
			'strict_type' => false,
			'fetch_mode' => true,
		]);
		ilogger()->info('connection id ' . spl_object_hash($connection));
		if ($connection === false || !empty($connection->connect_errno)) {
			throw new \RuntimeException($connection->connect_error);
		}
		return $connection;
	}

	public function release($connection) {
		$connection = $connection->getPdo();
		if ($connection instanceof \Closure) {
			$connection = $connection->getReadPdo();
		}
		ilogger()->info('pool release connection ');
		return parent::release($connection);
	}
}
