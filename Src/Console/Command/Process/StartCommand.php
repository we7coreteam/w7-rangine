<?php

namespace W7\Console\Command\Process;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Process\Pool\IndependentPool;
use W7\Core\Process\ProcessService;

class StartCommand extends CommandAbstract {
	protected $description = 'start user process service';

	protected function configure() {
		$this->addOption('--group', '-g', InputOption::VALUE_OPTIONAL, 'the crontab group');
	}

	public function handle($options) {
		if(empty($options['group'])) {
			$options['group'] = 'default';
		}
		if(!isset(iconfig()->getUserConfig('process')['process'][$options['group']])) {
			throw new \Exception('group error');
		}

		ProcessService::group($options['group']);
		(new ProcessService())->registerPool(IndependentPool::class)->start();
	}
}