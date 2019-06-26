<?php

namespace W7\Console\Command\Crontab;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Crontab\CrontabService;
use W7\Core\Crontab\Process\CrontabDispatcher;
use W7\Core\Exception\CommandException;
use W7\Core\Process\Pool\IndependentPool;

class StartCommand extends CommandAbstract {
	protected $description = 'start crontab service';

	protected function configure() {
		$this->addOption('--group', '-g', InputOption::VALUE_REQUIRED, 'the task group');
	}

	public function handle($options) {
		if (empty($options['group'])) {
			throw new CommandException('please input option group');
		}

		CrontabDispatcher::group($options['group']);
		(new CrontabService())->registerPool(IndependentPool::class)->start();
	}
}