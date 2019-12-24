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

namespace W7\Core\Session\Event;

use W7\Core\Session\Session;

class SessionStartEvent {
	public $session;

	public function __construct(Session $session) {
		$this->session = $session;
	}
}
