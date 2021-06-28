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

namespace W7\Tcp\Collector;

use W7\Core\Helper\Traiter\InstanceTrait;

class FdCollector {
	use InstanceTrait;

	protected $fdMap = [];

	public function set($fd, $data) {
		$this->fdMap[$fd] = $data;
	}

	public function get($fd, $default = []) {
		return $this->fdMap[$fd] ?? $default;
	}

	public function delete($fd) {
		if (isset($this->fdMap[$fd])) {
			unset($this->fdMap[$fd]);
		}
	}

	public function all() {
		return $this->fdMap;
	}

	public function clear() {
		$this->fdMap = [];
	}
}
