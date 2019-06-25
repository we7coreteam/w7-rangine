<?php

namespace W7\Console\Command\Process;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Crontab\Process\CrontabDispatcher;
use W7\Core\Crontab\Server\CrontabServer;
use W7\Core\UserProcess\Server\ProcessServer;

class StartCommand extends CommandAbstract {
	protected $description = 'start user process server';

	protected function configure() {
		$this->addOption('--group', '-g', InputOption::VALUE_OPTIONAL, 'the crontab group');
	}

	public function handle($options) {
//		if(!empty($options['group'])) {
//			CrontabDispatcher::group($options['group']);
//		}
		(new ProcessServer())->start();
	}
}