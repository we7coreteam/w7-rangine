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

namespace W7\Core\Redis\Pool;

use W7\Core\Redis\ConnectionResolver;
use W7\Core\Pool\CoPoolAbstract;

class Pool extends CoPoolAbstract {
	protected $type = 'redis';

	public function createConnection() {
		return $this->getContainer()->get(ConnectionResolver::class)->createConnection($this->getPoolName(), false);
	}

	public function getConnection() {
		$connect = parent::getConnection();
		try {
			$connect->ping();
			return $connect;
		} catch (\Throwable $e) {
			return $this->createConnection();
		}
	}
}
