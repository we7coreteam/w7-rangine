<?php

namespace W7\Console\Command\Crontab;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Crontab\Process\CrontabDispatcher;
use W7\Core\Crontab\Server\CrontabServer;

class StartCommand extends CommandAbstract {
	protected $description = 'start crontab server';

	protected function configure() {
		$this->addOption('--group', '-g', InputOption::VALUE_OPTIONAL, 'the crontab group');
	}

	public function handle($options) {
		if(!empty($options['group'])) {
			CrontabDispatcher::group($options['group']);
		}
		(new CrontabServer())->start();
	}
}