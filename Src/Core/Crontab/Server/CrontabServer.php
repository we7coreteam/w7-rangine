<?php

namespace W7\Core\Crontab\Server;

use W7\Core\Crontab\Process\CrontabDispatcher;
use W7\Core\Crontab\Process\CrontabExecutor;
use W7\Core\Process\Pool;

class CrontabServer {
	private $processManager;

	public function __construct() {
		$config = iconfig()->getUserConfig('crontab');
		$this->processManager = new Pool(SWOOLE_IPC_MSGQUEUE, $config['setting']['msg_key']);
		$this->processManager->addProcess('crontab_dispatch', CrontabDispatcher::class, 1);
		$this->processManager->addProcess('crontab_executor', CrontabExecutor::class, $config['setting']['worker_num']);
	}

	public function start() {
		$this->processManager->start();
	}
}