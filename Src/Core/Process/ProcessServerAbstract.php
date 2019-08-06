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

namespace W7\Core\Process;

use W7\Core\Process\Pool\DependentPool;
use W7\Core\Process\Pool\IndependentPool;
use W7\Core\Process\Pool\PoolAbstract;
use W7\Core\Server\ServerAbstract;

abstract class ProcessServerAbstract extends ServerAbstract {
	/**
	 * @var PoolAbstract
	 */
	protected $pool;

	protected function checkSetting() {
		$setting = array_merge($this->setting, $this->connection);
		return [
			'pid_file' => $setting['pid_file'],
			'worker_num' => $setting['worker_num'],
			'message_queue_key' => $setting['message_queue_key'],
			'daemonize' => $setting['daemonize']
		];
	}

	protected function register() {
	}

	public function getStatus() {
		$pidFile = $this->setting['pid_file'];
		if (file_exists($pidFile)) {
			$pids = explode(',', file_get_contents($pidFile));
		}
		return [
			'host' => $this->connection['host'] ?? '',
			'port' => $this->connection['port'] ?? '',
			'type' => $this->connection['sock_type'] ?? '',
			'workerNum' => $this->setting['worker_num'],
			'masterPid' => !empty($pids[0]) ? $pids[0] : 0
		];
	}

	public function start() {
		$this->pool = new IndependentPool($this->checkSetting());
		$this->register();
		$this->pool->start();
	}

	public function listener($server = null) {
		$this->pool = new DependentPool($this->checkSetting());
		$this->register();
		$this->pool->start();
	}

	public function getPool() : PoolAbstract {
		return $this->pool;
	}
}
