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

use W7\Core\Pool\PoolFactoryAbstract;

class PoolFactory extends PoolFactoryAbstract {
	protected function getPoolInstance($name): Pool {
		$pool = new Pool($name);
		$pool->setMaxCount($this->poolConfig[$name]['max'] ?? 1);

		return $pool;
	}
}
