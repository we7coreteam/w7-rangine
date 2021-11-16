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

namespace W7\Core\Pool;

use W7\Contract\Pool\PoolFactoryInterface;
use W7\Contract\Pool\PoolInterface;

abstract class PoolFactoryAbstract implements PoolFactoryInterface {
	protected array $poolConfig;
	protected array $pools;

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
	 * @return PoolInterface
	 */
	public function getCreatedPool($name) : PoolInterface {
		return $this->pools[$name];
	}

	/**
	 * @param $name
	 * @return PoolInterface
	 */
	public function getPool($name) : PoolInterface {
		if (!empty($this->pools[$name])) {
			return $this->pools[$name];
		}

		$pool = $this->getPoolInstance($name);
		$this->pools[$name] = $pool;
		return $this->pools[$name];
	}

	/**
	 * @param $name
	 * @return CoPoolAbstract
	 */
	abstract protected function getPoolInstance($name): CoPoolAbstract;
}
