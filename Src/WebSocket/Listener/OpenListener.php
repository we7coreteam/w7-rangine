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

use Swoole\Http\Request;
use Swoole\WebSocket\Server;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\SwooleEvent;

class OpenListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $request) = $params;
		$this->onOpen($server, $request);
	}

	/**
	 * @param Server $server
	 * @param Request $request
	 * @throws \Exception
	 */
	private function onOpen(Server $server, Request $request): void {
		//做数据绑定和记录
		ievent(SwooleEvent::ON_USER_BEFORE_OPEN, [$server, $request]);
	}
}
