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

use W7\Core\Dispatcher\EventDispatcher;
use W7\Core\Server\SwooleEvent;

class WorkerShutDownListener extends ListenerAbstract {
	public function run(...$params) {
		$startedServers = iconfig()->getUserConfig('app')['setting']['started_servers'] ?? [];
		foreach ($startedServers as $startedServer) {
			$listener = sprintf('\\W7\\%s\\Listener\\%sListener', ucfirst($startedServer), ucfirst(SwooleEvent::ON_USER_AFTER_WORKER_SHUTDOWN));
			iloader()->get(EventDispatcher::class)->listen(SwooleEvent::ON_USER_AFTER_WORKER_SHUTDOWN, $listener);
		}

		ievent(SwooleEvent::ON_USER_AFTER_WORKER_SHUTDOWN, $params);
	}
}
