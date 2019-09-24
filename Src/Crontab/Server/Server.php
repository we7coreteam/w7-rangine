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
use W7\Core\Server\ServerEnum;
use W7\Crontab\Process\CrontabDispatcher;
use W7\Crontab\Process\CrontabExecutor;

class Server extends ProcessServerAbstract {
	public function __construct() {
		$crontabConfig = iconfig()->getUserConfig($this->getType());
		$supportServers = iconfig()->getServer();
		$supportServers[$this->getType()] = $crontabConfig['setting'] ?? [];
		iconfig()->setUserConfig('server', $supportServers);

		parent::__construct();
	}

	public function getType() {
		return ServerEnum::TYPE_CRONTAB;
	}

	protected function getSetting() {
		$setting = parent::getSetting();
		$setting['ipc_type'] = SWOOLE_IPC_MSGQUEUE;
		$setting['message_queue_key'] =(int)($setting['message_queue_key'] ?? 0);
		$setting['message_queue_key'] = $setting['message_queue_key'] > 0 ? $setting['message_queue_key'] : irandom(6, true);
		return $setting;
	}

	protected function register() {
		$this->pool->registerProcess('crontab_dispatch', CrontabDispatcher::class, 1);
		$this->pool->registerProcess('crontab_executor', CrontabExecutor::class, $this->setting['worker_num'] ?? 1);
	}
}
