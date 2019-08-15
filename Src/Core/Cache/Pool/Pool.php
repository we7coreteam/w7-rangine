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

namespace W7\Core\Cache\Pool;

use W7\Core\Pool\CoPoolAbstract;

class Pool extends CoPoolAbstract {
	private $creator;
	protected $type = 'cache';

	public function setCreator($creator) {
		$this->creator = $creator;
	}

	public function createConnection() {
		if (empty($this->creator)) {
			throw new \RuntimeException('Invalid cache creator');
		}
		$connectionClass = $this->creator;
		$connection = $connectionClass::getHandler($this->config);
		$connection->poolName = sprintf('%s:%s', $this->config['driver'], $this->poolName);
		return $connection;
	}

	public function getConnection() {
		$connect = parent::getConnection();
		try {
			$connect->alive();
			return $connect;
		} catch (\Throwable $e) {
			return $this->createConnection();
		}
	}
}
