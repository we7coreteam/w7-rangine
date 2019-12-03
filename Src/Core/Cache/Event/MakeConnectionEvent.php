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

namespace W7\Core\Cache\Event;

use W7\Core\Cache\Handler\HandlerAbstract;

class MakeConnectionEvent {
	public $name;

	/**
	 * @var HandlerAbstract
	 */
	public $handler;

	public function __construct($name, $handler) {
		$this->name = $name;
		$this->handler = $handler;
	}
}
