<?php

namespace W7\Console\Command\Http;

class StartCommand extends HttpCommandAbstract {
	protected function configure() {
		$this->addOption('--enable-tcp', null, null, 'enable tcp service');
		$this->setDescription('start the http service');
	}

	protected function handle($options) {
		$this->start($options);
	}
}