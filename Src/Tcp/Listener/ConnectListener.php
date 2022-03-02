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

namespace W7\Tcp\Listener;

use Swoole\Server;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Http\Message\Server\Request as Psr7Request;

class ConnectListener extends ListenerAbstract {
	public function run(...$params) {
		[$server, $fd, $reactorId] = $params;
		$this->onConnect($server, $fd, $reactorId);
	}

	private function onConnect(Server $server, $fd, $reactorId) {
		$this->getEventDispatcher()->dispatch(ServerEvent::ON_USER_AFTER_OPEN, [$server, $fd, new Psr7Request('', ''), ServerEnum::TYPE_TCP]);
	}
}
