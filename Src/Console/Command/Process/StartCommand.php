<?php

namespace W7\Console\Command\Process;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Exception\CommandException;
use W7\Core\Process\Pool\IndependentPool;
use W7\Core\Process\ProcessService;

class StartCommand extends CommandAbstract {
	protected $description = 'start user process service';

	protected function configure() {
		$this->addOption('--process', null, InputOption::VALUE_REQUIRED, 'user-defined process name');
	}

	public function handle($options) {
		if (empty($options['process'])) {
			$options['process'] = ienv('START_USER_PROCESS');
		}
		if (empty($options['process'])) {
			throw new CommandException('please input option process');
		}

		$processService = new ProcessService();
		$processService->setUserProcess($options['process']);
		$processService->registerPool(IndependentPool::class)->start();
	}
}