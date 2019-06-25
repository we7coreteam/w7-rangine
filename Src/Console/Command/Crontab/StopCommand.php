<?php

namespace W7\Console\Command\Crontab;

use W7\Console\Command\CommandAbstract;
use W7\Core\Crontab\Server\CrontabServer;

class StopCommand extends CommandAbstract {
	protected $description = 'stop crontab server';

	public function handle($options) {
		(new CrontabServer())->stop();
	}
}