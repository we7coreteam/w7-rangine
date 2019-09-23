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

namespace W7\WebSocket\Session;

use W7\Core\Session\Session;
use W7\WebSocket\Collector\CollectorAbstract;

class SessionCollector extends CollectorAbstract {
	protected static $name = 'session';

	public function set($fd, $request) {
		$session = new Session();
		$session->start($request);

		parent::set($fd, $session);
	}

	public function del($fd) {
		/**
		 * @var Session $session
		 */
		if ($session = parent::get($fd)) {
			$session->destroy();
		}
	}
}
