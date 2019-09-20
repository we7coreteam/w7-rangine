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

namespace W7\Core\Listener;

use W7\App;

class WorkerStartListener implements ListenerInterface {
	public function run(...$params) {
		if (ini_get('opcache.enable') || ini_get('opcache.enable_cli')) {
			opcache_reset();
		}

		\isetProcessTitle('w7-rangine ' . App::$server->getType() . (App::$server->server->taskworker ? ' task' : '')  . ' worker process');
	}
}
