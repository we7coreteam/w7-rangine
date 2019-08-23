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

namespace W7\Core\Database;

use Illuminate\Database\Connectors\MySqlConnector;
use W7\Core\Database\Connector\SwooleMySqlConnector;
use W7\Core\Database\Pool\Pool;

class ConnectorManager {
	private $poolConfig;
	private $pool;
	private $mySqlConnector; //swoole的协程mysql连接
	private $pdoConnector; //laravel原始的Pdo连接
	private $defaultConnection;

	public function __construct() {
		$this->poolConfig = \iconfig()->getUserAppConfig('pool')['database'] ?? [];
	}

	/**
	 * ConnectionFactory 类调用，用于实例化数据库连接
	 * 此处根据传来的host，去创建相应的数据库连接池，然后返回连接
	 * @param array $config
	 */
	public function connect(array $config) {
		//未设置连接池时，直接返回数据连接对象
		if (empty($this->poolConfig[$config['name']]) || empty($this->poolConfig[$config['name']]['enable'])) {
			return $this->getDefaultConnection($config);
		}
		$pool = $this->getPool($config['name'], $config);
		return $pool->getConnection();
	}

	public function getCreatedPool($name) {
		return $this->pool[$name];
	}

	/**
	 * @param $name
	 * @return Pool
	 */
	private function getPool($name, $option = []) {
		if (!empty($this->pool[$name])) {
			return $this->pool[$name];
		}
		$pool = new Pool($name);
		$pool->setConfig($option);
		$pool->setCreator($this->getDefaultConnector($option['driver']));
		$pool->setMaxCount($this->poolConfig[$name]['max']);

		$this->pool[$name] = $pool;
		return $this->pool[$name];
	}

	private function getDefaultConnector($driver = 'swoolemysql') {
		if ($driver == 'swoolemysql') {
			if (empty($this->mySqlConnector)) {
				$this->mySqlConnector = new SwooleMySqlConnector();
			}
			return $this->mySqlConnector;
		} elseif ($driver == 'mysql') {
			if (empty($this->pdoConnector)) {
				$this->pdoConnector = new MySqlConnector();
			}
			return $this->pdoConnector;
		}
		throw new \RuntimeException('Invalid driver');
	}

	private function getDefaultConnection($config) {
		ilogger()->channel('database')->debug('create connection without pool');

		$this->defaultConnection = $this->getDefaultConnector($config['driver'])->connect($config);
		return $this->defaultConnection;
	}
}
