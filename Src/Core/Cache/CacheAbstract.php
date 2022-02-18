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

namespace W7\Core\Cache;

use Psr\SimpleCache\CacheInterface;
use W7\Core\Cache\Handler\HandlerAbstract;

abstract class CacheAbstract implements CacheInterface {
	protected $name;
	protected $prefix;
	/**
	 * @var HandlerAbstract
	 */
	protected $handler;

	public function __construct($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function setPrefix($prefix) {
		$this->prefix = $prefix;
	}

	public function setHandler(HandlerAbstract $handler) {
		$this->handler = $handler;
	}

	protected function warpKey($key) {
		return $this->prefix . $key;
	}
}
