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

namespace W7\Core\Pool\Event;

use W7\Core\Pool\CoPoolAbstract;

abstract class PoolEventAbstract {
	public $type;
	public $name;
	/**
	 * @var CoPoolAbstract
	 */
	public $pool;

	public function __construct($type, $name, CoPoolAbstract $pool) {
		$this->type = $type;
		$this->name = $name;
		$this->pool = $pool;
	}
}
