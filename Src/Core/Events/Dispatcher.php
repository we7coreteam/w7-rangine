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

namespace W7\Core\Events;

use Illuminate\Events\Dispatcher as DispatcherAbstract;
use Illuminate\Support\Str;

class Dispatcher extends DispatcherAbstract {
	protected function parseClassCallable($listener) {
		return Str::parseCallback($listener, 'run');
	}

	public function setContainer($container) {
		$this->container = $container;
	}
}
