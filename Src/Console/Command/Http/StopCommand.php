<?php

namespace W7\Console\Command\Http;

class StopCommand extends HttpCommandAbstract {
	protected function configure() {
		$this->setDescription('stop http service');
	}

	protected function handle($options) {
		$this->stop();
	}
}