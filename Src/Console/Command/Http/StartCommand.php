<?php

namespace W7\Console\Command\Http;

class StartCommand extends HttpCommandAbstract {
	protected function configure() {
		$this->addOption('--enable-tcp', null, null, '开启tcp服务');
		$this->setDescription('启动http服务');
	}

	protected function handle($options) {
		$this->start($options);
	}
}