<?php

namespace W7\Console\Command\Http;

class RestartCommand extends HttpCommandAbstract {
	protected function configure() {
		$this->setDescription('重启http服务');
	}

	protected function handle($options) {
		// TODO: Implement handle() method.
		$this->restart();
	}
}