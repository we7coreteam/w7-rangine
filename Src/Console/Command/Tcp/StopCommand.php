<?php

namespace W7\Console\Command\Tcp;

class StopCommand extends TcpCommandAbstract {
	protected function configure() {
		$this->setDescription('停止tcp服务');
	}

	protected function handle($options) {
		$this->stop();
	}
}