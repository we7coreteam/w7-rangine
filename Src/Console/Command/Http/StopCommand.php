<?php

namespace W7\Console\Command\Http;

class StopCommand extends HttpCommandAbstract {
	protected function configure() {
		$this->setDescription('停止http服务');
	}

	protected function handle($options) {
		$this->stop();
	}
}