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
		$this->addOption('--name', null, InputOption::VALUE_REQUIRED, 'user-defined process name');
	}

	public function handle($options) {
		if (empty($options['name'])) {
			throw new CommandException('please input option name');
		}

		$config = iconfig()->getUserConfig('process');
		$config['appoint_process'] = $options['name'];
		iconfig()->setUserConfig('process', $config);

		(new ProcessService())->registerPool(IndependentPool::class)->start();
	}
}