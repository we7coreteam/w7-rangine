<?php

namespace W7\Console\Command\Crontab;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Crontab\CrontabService;
use W7\Core\Process\Pool\IndependentPool;

class StartCommand extends CommandAbstract {
	protected $description = 'start crontab service';

	protected function configure() {
		$this->addOption('--group', '-g', InputOption::VALUE_OPTIONAL, 'the crontab group');
	}

	public function handle($options) {
		if(empty($options['group'])) {
			$options['group'] = 'default';
		}
		if(!isset(iconfig()->getUserConfig('crontab')['task'][$options['group']])) {
			throw new \Exception('group error');
		}

		CrontabService::group($options['group']);
		(new CrontabService())->registerPool(IndependentPool::class)->start();
	}
}