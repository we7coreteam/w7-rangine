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

use Swoole\Server;

abstract class BeforeStartListenerAbstract extends UserListenerAbstract {
	protected Server $server;

	public function __construct(...$params) {
		$this->server = $params[0];
		$this->serverType = $params[1];
	}
}
