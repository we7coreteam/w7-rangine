<?php

namespace W7\WebSocket\Listener;

use Swoole\Http\Request;
use Swoole\WebSocket\Server;
use W7\Core\Listener\ListenerAbstract;

class OpenListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $request) = $params;
		$this->onOpen($server, $request);
	}

	/**
	 * @param Psr7Request $request
	 * @param int         $fd
	 *
	 * @throws Throwable
	 */
	private function onOpen(Server $server, Request $request): void {
		//做数据绑定和记录
	}
}