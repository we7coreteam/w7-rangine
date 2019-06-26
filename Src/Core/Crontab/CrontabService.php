<?php

namespace W7\Core\Crontab;

use W7\Core\Crontab\Process\CrontabDispatcher;
use W7\Core\Crontab\Process\CrontabExecutor;
use W7\Core\Process\Pool\PoolServiceAbstract;

class CrontabService extends PoolServiceAbstract {
	const DEFAULT_PID_FILE = '/tmp/swoole_crontab.pid';

	public function __construct() {
		$this->config = iconfig()->getUserConfig('crontab');
		$this->config['setting']['ipc_type'] = SWOOLE_IPC_MSGQUEUE;
		$this->config['setting']['pid_file'] = empty($this->config['setting']['pid_file']) ? self::DEFAULT_PID_FILE : $this->config['setting']['pid_file'];
		$this->poolConfig = $this->config['setting'];
	}

	public function start() {
		if (empty(iconfig()->getUserConfig('crontab')['task'][CrontabDispatcher::$group])) {
			//表示是跟随server启动的方式,如果任务为空,不启动process
			if (CrontabDispatcher::$group === 'default') {
				return false;
			}
			throw new \Exception('task list cannot be empty');
		}

		$this->processPool->registerProcess('crontab_dispatch', CrontabDispatcher::class, 1);
		$this->processPool->registerProcess('crontab_executor', CrontabExecutor::class, $this->config['setting']['worker_num']);
		$this->processPool->start();
	}
}