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
use W7\Core\Server\ServerEvent;

class WorkerStartListener extends ListenerAbstract {
	/**
	 * @throws \Exception
	 */
	public function run(...$params) {
		mt_srand();

		if (ini_get('opcache.enable') || ini_get('opcache.enable_cli')) {
			opcache_reset();
		}

		\isetProcessTitle(App::$server->getPname(). App::$server->getType() . (App::$server->server->taskworker ? ' task' : '')  . ' worker process');

		$this->getEventDispatcher()->dispatch(ServerEvent::ON_USER_AFTER_WORKER_START, $params);
	}
}
