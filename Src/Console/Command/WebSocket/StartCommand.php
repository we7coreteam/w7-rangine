<?php

namespace W7\Console\Command\WebSocket;

class StartCommand extends WebSocketCommandAbstract {
	protected $description = 'start tcp service';

	protected function handle($options) {
		$this->start($options);
	}
}