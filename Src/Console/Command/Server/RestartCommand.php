<?php

namespace W7\Console\Command\Server;

class RestartCommand extends ServerCommandAbstract {
	protected $description = 'restart server';

	protected function handle($options) {
		parent::handle($options);
		$this->restart();
	}
}