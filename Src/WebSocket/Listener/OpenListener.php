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

namespace W7\WebSocket\Listener;

use Swoole\WebSocket\Server;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Http\Message\Server\Request as Psr7Request;

class OpenListener extends ListenerAbstract {
	/**
	 * @throws \Exception
	 */
	public function run(...$params) {
		[$server, $psr7Request] = $params;
		$this->onOpen($server, $psr7Request);
	}

	/**
	 * @throws \Exception
	 */
	private function onOpen(Server $server, Psr7Request $psr7Request): void {
		$this->getEventDispatcher()->dispatch(ServerEvent::ON_USER_AFTER_OPEN, [$server, $psr7Request->getSwooleRequest()->fd, $psr7Request->getSwooleRequest(), ServerEnum::TYPE_WEBSOCKET]);
	}
}
