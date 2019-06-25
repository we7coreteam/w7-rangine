<?php

namespace W7\Core\Crontab\Server;

use W7\Core\Crontab\Process\CrontabDispatcher;
use W7\Core\Crontab\Process\CrontabExecutor;
use W7\Core\Process\Pool\Pool;

class CrontabServer {
	const DEFAULT_PID_FILE = '/tmp/swoole_crontab.pid';
	private $processPool;
	private $config;

	public function __construct() {
		$this->config = iconfig()->getUserConfig('crontab');
		$this->config['setting']['ipc_type'] = SWOOLE_IPC_MSGQUEUE;
		$this->config['setting']['pid_file'] = empty($this->config['setting']['pid_file']) ? self::DEFAULT_PID_FILE : $this->config['setting']['pid_file'];

		$this->processPool = new Pool($this->config['setting']);
	}

	public function start() {
		$this->processPool->addProcess('crontab_dispatch', CrontabDispatcher::class, 1);
		$this->processPool->addProcess('crontab_executor', CrontabExecutor::class, $this->config['setting']['worker_num']);

		$this->processPool->start();
	}

	public function stop() {
		$this->processPool->stop();
	}
}