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

abstract class CollectorAbstract {
	protected static $name;
	protected $fds = [];

	public static function getName() {
		return static::$name;
	}

	public function set($fd, $something) {
		$this->fds[$fd] = $something;
	}

	public function get($fd) {
		return $this->fds[$fd] ?? null;
	}

	public function del($fd) {
		if (isset($this->fds[$fd])) {
			unset($this->fds[$fd]);
		}
	}
}
