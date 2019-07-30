<?php

namespace W7\Console\Command\WebSocket;

use W7\Core\Server\ServerCommandAbstract;
use W7\WebSocket\Server\Server;

abstract class WebSocketCommandAbstract extends ServerCommandAbstract {
	protected function createServer() {
		return new Server();
	}
}