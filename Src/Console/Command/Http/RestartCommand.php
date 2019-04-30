<?php

namespace W7\Console\Command\Http;

class RestartCommand extends HttpCommandAbstract {
	protected function configure() {
		$this->setDescription('restart the http service');
	}

	protected function handle($options) {
		// TODO: Implement handle() method.
		$this->restart();
	}
}