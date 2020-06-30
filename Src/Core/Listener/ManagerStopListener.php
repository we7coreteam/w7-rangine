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

use W7\Core\Facades\Event;
use W7\Core\Server\ServerEvent;

class ManagerStopListener extends ListenerAbstract {
	public function run(...$params) {
		Event::dispatch(ServerEvent::ON_USER_AFTER_MANAGER_STOP, $params);
	}
}
