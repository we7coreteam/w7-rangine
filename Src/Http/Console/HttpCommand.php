<?php

namespace W7\Http\Console;

use W7\Console\Command\Server\ServerCommandAbstract;
use W7\Http\Server\Server;

class HttpCommand extends ServerCommandAbstract {
	protected function configure() {
		parent::configure();
		$this->addOption('--enable-tcp', null, null, '开启tcp服务');
	}

	protected function createServer() {
		$server = new Server();
		return $server;
	}
}