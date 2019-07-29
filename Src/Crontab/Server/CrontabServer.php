<?php

namespace W7\Crontab\Server;

use W7\Crontab\Process\CrontabDispatcher;
use W7\Crontab\Process\CrontabExecutor;
use W7\Core\Process\Pool\PoolServerAbstract;

class CrontabServer extends PoolServerAbstract {
	protected function init() {
		$this->config = iconfig()->getUserConfig('crontab');
		$this->config['setting']['ipc_type'] = SWOOLE_IPC_MSGQUEUE;
		$this->config['setting']['auto_start'] = $this->config['setting']['auto_start'] ?? false;

		$this->poolConfig = $this->config['setting'];
	}

	protected function register(): bool {
		if (!CrontabDispatcher::getTasks()) {
			return false;
		}

		$this->processPool->registerProcess('crontab_dispatch', CrontabDispatcher::class, 1);
		$this->processPool->registerProcess('crontab_executor', CrontabExecutor::class, $this->config['setting']['worker_num']);

		return true;
	}

	public function getType() {
		return parent::TYPE_CRONTAB;
	}
}