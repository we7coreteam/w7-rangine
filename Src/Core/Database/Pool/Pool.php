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

namespace W7\Core\Database\Pool;

use W7\Core\Database\ConnectionResolver;
use W7\Core\Pool\CoPoolAbstract;

class Pool extends CoPoolAbstract {
	protected $type = 'database';

	public function createConnection() {
		return $this->getContainer()->singleton(ConnectionResolver::class)->createConnection($this->getPoolName(), false);
	}
}
