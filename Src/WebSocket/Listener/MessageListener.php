<?php

namespace W7\WebSocket\Listener;

use Swoole\Websocket\Frame;
use Swoole\Websocket\Server;
use W7\Core\Listener\ListenerAbstract;
use W7\WebSocket\Parser\JsonParser;
use W7\WebSocket\Parser\ParserInterface;


class MessageListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $frame) = $params;
		$this->onMessage($server, $frame);
	}

	private function onMessage(Server $server, Frame $frame): void {
		var_dump($server);
		var_dump($frame);
	}
}
