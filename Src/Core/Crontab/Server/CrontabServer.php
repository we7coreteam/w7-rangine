<?php

namespace W7\Core\Crontab\Server;

use W7\Core\Crontab\Process\CrontabDispatcher;
use W7\Core\Crontab\Process\CrontabExecutor;
use W7\Core\Process\Pool;

class CrontabServer {
	const DEFAULT_PID_FILE = '/tem/swoole_crontab.pid';
	private $processManager;
	private $config;

	public function __construct() {
		$this->config = iconfig()->getUserConfig('crontab');

		$this->processManager = new Pool(SWOOLE_IPC_MSGQUEUE, $this->config['setting']['msg_key']);
		$this->processManager->addProcess('crontab_dispatch', CrontabDispatcher::class, 1);
		$this->processManager->addProcess('crontab_executor', CrontabExecutor::class, $this->config['setting']['worker_num']);
	}

	public function start() {
		$pidFile = $this->config['setting']['pid_file'] ?? self::DEFAULT_PID_FILE;
		file_put_contents($pidFile, getmypid());
		$this->processManager->start();
	}
}