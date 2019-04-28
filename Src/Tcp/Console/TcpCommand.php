<?php

namespace W7\Tcp\Console;

use W7\Console\Command\Server\ServerCommandAbstract;
use W7\Tcp\Server\Server;

class TcpCommand extends ServerCommandAbstract {
	public function createServer() {
		$server = new Server();
		return $server;
	}
}