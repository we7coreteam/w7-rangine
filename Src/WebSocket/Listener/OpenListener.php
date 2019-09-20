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
use W7\WebSocket\Collector\CollectorManager;
use W7\Http\Message\Server\Request as Psr7Request;

class OpenListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $request, $psr7Request) = $params;
		$this->onOpen($server, $request, $psr7Request);
	}

	/**
	 * @param Server $server
	 * @param Request $request
	 * @param Psr7Request $psr7Request
	 */
	private function onOpen(Server $server, Request $request, Psr7Request $psr7Request): void {
		//做数据绑定和记录
		iloader()->get(CollectorManager::class)->set($request->fd, $psr7Request);
		ievent(SwooleEvent::ON_USER_BEFORE_OPEN, [$server, $request]);
	}
}
