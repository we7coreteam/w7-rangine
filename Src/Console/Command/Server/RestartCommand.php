<?php

namespace W7\Console\Command\Server;

class RestartCommand extends ServerCommandAbstract {
	protected $description = 'restart server';

	protected function handle($options) {
		// TODO: Implement handle() method.
		$this->restart();
	}
}