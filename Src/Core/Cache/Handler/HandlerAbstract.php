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

namespace W7\Core\Cache\Handler;

use Illuminate\Database\DetectsLostConnections;
use Psr\SimpleCache\CacheInterface;

abstract class HandlerAbstract implements CacheInterface {
	use DetectsLostConnections;

	abstract public static function getHandler($config) : HandlerAbstract;

	public function pack($data) {
		return is_numeric($data) ? $data : serialize($data);
	}

	public function unpack($data) {
		return is_numeric($data) ? $data : unserialize($data);
	}

	public function alive() {
		return true;
	}
}
