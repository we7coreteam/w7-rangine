<?php

namespace W7\Console\Command\Tcp;

class RestartCommand extends TcpCommandAbstract {
	protected function configure() {
		$this->setDescription('重启tcp服务');
	}

	protected function handle($options) {
		// TODO: Implement handle() method.
		$this->restart();
	}
}