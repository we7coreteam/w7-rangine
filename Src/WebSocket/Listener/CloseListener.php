<?php

namespace W7\WebSocket\Listener;

use Swoole\Server;
use W7\Core\Listener\ListenerAbstract;


class CloseListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $fd, $reactorId) = $params;
		$this->onClose($server, $fd, $reactorId);
	}

	private function onClose(Server $server, int $fd, int $reactorId): void {

	}
}
