<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

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

	public function getType() {
		return parent::TYPE_CRONTAB;
	}

	protected function register(): bool {
		if (!CrontabDispatcher::getTasks()) {
			return false;
		}

		$this->processPool->registerProcess('crontab_dispatch', CrontabDispatcher::class, 1);
		$this->processPool->registerProcess('crontab_executor', CrontabExecutor::class, $this->config['setting']['worker_num']);

		return true;
	}
}
