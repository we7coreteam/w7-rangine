<?php

namespace W7\Console\Command\Server;

class StopCommand extends ServerCommandAbstract {
	protected $description = 'stop server';

	protected function handle($options) {
		parent::handle($options);
		$this->stop();
	}
}