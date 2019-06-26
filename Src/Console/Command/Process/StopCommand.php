<?php

namespace W7\Console\Command\Process;

use W7\Console\Command\CommandAbstract;
use W7\Core\Process\Pool\IndependentPool;
use W7\Core\Process\ProcessService;

class StopCommand extends CommandAbstract {
	protected $description = 'stop user process service';

	public function handle($options) {
		(new ProcessService())->registerPool(IndependentPool::class)->stop();
	}
}