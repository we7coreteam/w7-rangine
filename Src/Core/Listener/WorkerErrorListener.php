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

use W7\Core\Server\SwooleEvent;

class WorkerErrorListener implements ListenerInterface {
	public function run(...$params) {
		ievent(SwooleEvent::ON_USER_AFTER_WORKER_ERROR);
	}
}
