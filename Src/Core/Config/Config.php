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

namespace W7\Core\Config;

use Illuminate\Support\Arr;
use W7\Contract\Config\RepositoryInterface;

class Config implements RepositoryInterface {
	private $payload = [];

	public function __construct(array $payload = []) {
		$this->payload = $payload;
	}

	public function set($key, $value = null) {
		return Arr::set($this->payload, $key, $value);
	}

	public function has($key) {
		return Arr::has($this->payload, $key);
	}

	public function get($key, $default = null) {
		return Arr::get($this->payload, $key, $default);
	}

	public function all() {
		return $this->payload;
	}
}
