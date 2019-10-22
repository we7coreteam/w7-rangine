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

namespace W7\Core\Process\Pool;

use Swoole\Process;
use Swoole\Process\Pool as PoolManager;

/**
 * 该进程池由独立的process manager管理
 * Class IndependentPool
 * @package W7\Core\Process\Pool
 */
class IndependentPool extends PoolAbstract {
	private $ipcType = SWOOLE_IPC_NONE;
	private $pidFile;
	private $daemon;
	private $events = [];

	protected function init() {
		$this->pidFile = $this->config['pid_file'];
		$this->daemon = $this->config['daemonize'] ?? false;
	}

	private function setDaemon() {
		if ($this->daemon) {
			Process::daemon(true, false);
		}
	}

	public function start() {
		if ($this->processFactory->count() == 0) {
			return false;
		}

		$this->setDaemon();

		if (SWOOLE_VERSION >= '4.4.0') {
			$server = new PoolManager($this->processFactory->count(), $this->ipcType, $this->mqKey, true);
		} else {
			$server = new PoolManager($this->processFactory->count(), $this->ipcType, $this->mqKey);
		}

		foreach ($this->events as $event => $handler) {
			$server->on($event, $handler);
		}

		file_put_contents($this->pidFile, getmypid());

		$server->start();
	}

	public function on($event, \Closure $handler) {
		$this->events[$event] = $handler;
	}
}
