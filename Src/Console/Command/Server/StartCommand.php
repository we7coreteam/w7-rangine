<?php

namespace W7\Console\Command\Server;

class StartCommand extends ServerCommandAbstract {
	protected $description = 'start server';

	protected function handle($options) {
		$this->start();
	}
}