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

use Illuminate\Database\Connectors\Connector;
use Psr\EventDispatcher\EventDispatcherInterface;
use W7\Core\Database\Event\MakeConnectionEvent;
use W7\Core\Database\Pool\Pool;

class ConnectorManager {
	private $poolConfig;
	private $pools;

	private static $connectors;
	private $connectorObjs = [];
	/**
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;

	public function __construct($poolConfig = []) {
		$this->poolConfig = $poolConfig;
	}

	public function setEventDispatcher(EventDispatcherInterface $eventDispatcher) {
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * ConnectionFactory 类调用，用于实例化数据库连接
	 * 此处根据传来的host，去创建相应的数据库连接池，然后返回连接
	 * @param array $config
	 */
	public function connect(array $config) {
		//未设置连接池时，直接返回数据连接对象
		if (!isCo() || empty($this->poolConfig[$config['name']]) || empty($this->poolConfig[$config['name']]['enable'])) {
			return $this->getDefaultConnection($config);
		}
		$pool = $this->getPool($config['name'], $config);
		return $pool->getConnection();
	}

	public function getCreatedPool($name) {
		return $this->pools[$name];
	}

	/**
	 * @param $name
	 * @param array $config
	 * @return mixed
	 */
	private function getPool($name, $config = []) {
		if (!empty($this->pools[$name])) {
			return $this->pools[$name];
		}
		$pool = new Pool($name);
		$pool->setConfig($config);
		$pool->setCreator($this->getDefaultConnector($config['driver']));
		$pool->setMaxCount($this->poolConfig[$name]['max'] ?? 1);

		$this->pools[$name] = $pool;
		return $this->pools[$name];
	}

	private function getDefaultConnector($driver = 'mysql') : Connector {
		if (empty(self::$connectors[$driver])) {
			throw new \RuntimeException('Invalid driver');
		}
		if (empty($this->connectorObjs[$driver])) {
			$class = self::$connectors[$driver];
			$connector = new $class();
			if (!($connector instanceof Connector)) {
				throw new \RuntimeException('connector ' . $class . ' must be instance Illuminate\Database\Connectors\Connector');
			}
			$this->connectorObjs[$driver] = $connector;
		}

		return $this->connectorObjs[$driver];
	}

	private function getDefaultConnection($config) {
		$defaultConnection = $this->getDefaultConnector($config['driver'])->connect($config);
		$this->eventDispatcher && $this->eventDispatcher->dispatch(new MakeConnectionEvent($config['name'], $defaultConnection));
		return $defaultConnection;
	}

	public static function registerConnector(string $driver, string $connectorClass) {
		self::$connectors[$driver] = $connectorClass;
	}
}
