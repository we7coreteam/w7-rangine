<?php

namespace W7\Console\Command\Process;

use W7\Console\Command\CommandAbstract;
use W7\Core\UserProcess\Server\ProcessServer;

class StopCommand extends CommandAbstract {
	protected $description = 'stop user process server';

	public function handle($options) {
		(new ProcessServer())->stop();
	}
}