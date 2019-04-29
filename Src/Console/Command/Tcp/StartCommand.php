<?php

namespace W7\Console\Command\Tcp;

class StartCommand extends TcpCommandAbstract {
	protected function configure() {
		$this->setDescription('启动tcp服务');
	}

	protected function handle($options) {
		$this->start($options);
	}
}