<?php

namespace W7\Console\Command\WebSocket;

class StopCommand extends WebSocketCommandAbstract {
	protected $description = 'stop tcp service';

	protected function handle($options) {
		$this->stop();
	}
}