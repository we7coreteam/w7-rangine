<?php

namespace W7\Core\Crontab;

use W7\Core\Crontab\Process\CrontabDispatcher;
use W7\Core\Crontab\Process\CrontabExecutor;
use W7\Core\Process\Pool\PoolServerAbstract;

class CrontabServer extends PoolServerAbstract {
	const DEFAULT_PID_FILE = '/tmp/swoole_crontab.pid';

	public function __construct() {
		$this->config = iconfig()->getUserConfig('crontab');
		$this->config['setting']['ipc_type'] = SWOOLE_IPC_MSGQUEUE;
		$this->config['setting']['auto_start'] = $this->config['setting']['auto_start'] ?? false;
		$this->config['setting']['pid_file'] = empty($this->config['setting']['pid_file']) ? self::DEFAULT_PID_FILE : $this->config['setting']['pid_file'];
		$this->poolConfig = $this->config['setting'];
	}

	public function getType() {
		return parent::TYPE_CRONTAB;
	}

	public function start() {
		if (!CrontabDispatcher::getTasks()) {
			return false;
		}

		$this->processPool->registerProcess('crontab_dispatch', CrontabDispatcher::class, 1);
		$this->processPool->registerProcess('crontab_executor', CrontabExecutor::class, $this->config['setting']['worker_num']);
		$this->processPool->start();
	}
}