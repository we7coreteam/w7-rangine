<?php

namespace W7\Http\Console;

use W7\Console\Command\Server\ServerCommandAbstract;
use W7\Http\Server\Server;

class HttpCommand extends ServerCommandAbstract {
	protected function configure() {
		parent::configure();
		$this->addOption('--enable-tcp');
	}

	protected function createServer() {
		$server = new Server();
		return $server;
	}
}