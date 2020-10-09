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

class PoolFactory {
	private $poolConfig;
	private $pools;

	public function __construct($poolConfig = []) {
		$this->poolConfig = $poolConfig;
	}

	public function getPoolConfig($name = null) {
		if (!$name) {
			return $this->poolConfig;
		}

		return $this->poolConfig[$name] ?? [];
	}

	/**
	 * @param $name
	 * @return Pool
	 */
	public function getCreatedPool($name) {
		return $this->pools[$name];
	}

	/**
	 * @param $name
	 * @return Pool
	 */
	public function getPool($name) {
		if (!empty($this->pools[$name])) {
			return $this->pools[$name];
		}
		$pool = new Pool($name);
		$pool->setMaxCount($this->poolConfig[$name]['max'] ?? 1);

		$this->pools[$name] = $pool;
		return $this->pools[$name];
	}
}
