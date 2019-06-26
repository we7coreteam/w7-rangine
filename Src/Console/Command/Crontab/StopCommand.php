<?php

namespace W7\Console\Command\Crontab;

use W7\Console\Command\CommandAbstract;
use W7\Core\Crontab\CrontabService;
use W7\Core\Process\Pool\IndependentPool;

class StopCommand extends CommandAbstract {
	protected $description = 'stop crontab service';

	public function handle($options) {
		(new CrontabService())->registerPool(IndependentPool::class)->stop();
	}
}