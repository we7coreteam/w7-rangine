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
		$this->addOption('--tasks', null, InputOption::VALUE_REQUIRED, 'task to execute');
	}

	public function handle($options) {
		if (empty($options['tasks'])) {
			//支持从env中直接配置要执行的task 也可以指定多个,按,隔开
			$options['tasks'] = ienv('CRONTAB_TASKS');
		}
		if (empty($options['tasks'])) {
			throw new CommandException('please input option tasks');
		}

		CrontabDispatcher::setTasks($options['tasks']);
		(new CrontabService())->registerPool(IndependentPool::class)->start();
	}
}