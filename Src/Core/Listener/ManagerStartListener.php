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
use W7\Core\Server\SwooleEvent;

class ManagerStartListener extends ListenerAbstract {
	public function run(...$params) {
		$this->setServerTitle($params[0]);
		ievent(SwooleEvent::ON_USER_AFTER_START);
	}

	private function setServerTitle($server) {
		\isetProcessTitle('w7-rangine ' . App::$server->getType() . ' manager process');
	}
}
