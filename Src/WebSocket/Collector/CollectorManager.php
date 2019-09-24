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

namespace W7\WebSocket\Collector;

use W7\Http\Message\Server\Request;

/**
 * 统一收集连接时的资源
 * 统一释放断开后的无用资源
 * Class CollectorManager
 * @package W7\WebSocket\Collector
 */
class CollectorManager {
	private $collectors = [];

	public function addCollect(CollectorAbstract $collector) {
		$this->collectors[$collector->getName()] = $collector;
	}

	public function delCollector($name) {
		if (!empty($this->collectors[$name])) {
			unset($this->collectors[$name]);
		}
	}

	public function getCollector($name) : CollectorAbstract {
		if (empty($this->collectors[$name])) {
			throw new \RuntimeException('collect ' . $name . ' not exists');
		}
		return $this->collectors[$name];
	}

	public function set($fd, Request $psr7Request) {
		/**
		 * @var CollectorAbstract $collector
		 */
		foreach ($this->collectors as $collector) {
			$collector->set($fd, $psr7Request);
		}
	}

	public function get($fd) {
		$data = [];
		/**
		 * @var CollectorAbstract $collector
		 */
		foreach ($this->collectors as $collector) {
			$data[$collector->getName()] = $collector->get($fd);
		}

		return $data;
	}

	public function del($fd) {
		/**
		 * @var CollectorAbstract $collector
		 */
		foreach ($this->collectors as $collector) {
			$collector->del($fd);
		}
	}
}
