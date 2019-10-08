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
	public static $masterServer = false;
	public static $onlyFollowMasterServer = false;
	/**
	 * @var PoolAbstract
	 */
	protected $pool;

	protected function register() {
	}

	protected function checkSetting() {
		$this->setting = array_merge($this->setting, $this->connection);
		if (empty($this->setting['pid_file'])) {
			throw new \RuntimeException('server pid_file error');
		}
		return true;
	}

	protected function getSetting() {
		return [
			'pid_file' => $this->setting['pid_file'],
			'worker_num' => $this->setting['worker_num'] ?? 1,
			'message_queue_key' => $this->setting['message_queue_key'] ?? null,
			'daemonize' => $this->setting['daemonize'] ?? false
		];
	}

	public function getStatus() {
		$setting = $this->getSetting();
		$pid = 0;
		if (file_exists($setting['pid_file'])) {
			$pid = file_get_contents($setting['pid_file']);
		}
		return [
			'host' => $this->connection['host'] ?? '',
			'port' => $this->connection['port'] ?? '',
			'type' => $this->connection['sock_type'] ?? '',
			'workerNum' => $setting['worker_num'],
			'masterPid' => $pid
		];
	}

	public function start() {
		$this->pool = new IndependentPool($this->getSetting());
		$this->register();
		return $this->pool->start();
	}

	public function listener($server = null) {
		$this->pool = new DependentPool($this->getSetting());
		$this->register();
		return $this->pool->start();
	}

	public function getPool() : PoolAbstract {
		return $this->pool;
	}
}
