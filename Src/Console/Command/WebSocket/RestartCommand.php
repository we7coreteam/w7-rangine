<?php

namespace W7\Console\Command\WebSocket;

class RestartCommand extends WebSocketCommandAbstract {
	protected $description = 'restart tcp service';

	protected function handle($options) {
		// TODO: Implement handle() method.
		$this->restart();
	}
}