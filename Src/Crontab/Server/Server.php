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

use W7\Core\Process\ProcessServerAbstract;
use W7\Crontab\Process\CrontabDispatcher;
use W7\Crontab\Process\CrontabExecutor;

class Server extends ProcessServerAbstract {
	public function getType() {
		return parent::TYPE_CRONTAB;
	}

	protected function getSetting() {
		$setting = parent::getSetting();
		$setting['ipc_type'] = SWOOLE_IPC_MSGQUEUE;
		return $setting;
	}

	protected function register() {
		$this->pool->registerProcess('crontab_dispatch', CrontabDispatcher::class, 1);
		$this->pool->registerProcess('crontab_executor', CrontabExecutor::class, $this->setting['worker_num'] ?? 1);
	}
}
