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
use W7\Core\Server\SwooleEvent;

/**
 * 该进程池由独立的process manager管理
 * Class IndependentPool
 * @package W7\Core\Process\Pool
 */
class IndependentPool extends PoolAbstract {
	private $ipcType = SWOOLE_IPC_NONE;
	private $pidFile;
	private $daemon;

	protected function init() {
		$this->pidFile = $this->config['pid_file'];
		$this->daemon = $this->config['daemonize'] ?? false;
	}

	private function setDaemon() {
		if ($this->daemon) {
			Process::daemon(true, false);
		}
	}

	private function setProcessName() {
		isetProcessTitle('w7rangine_pool_master');
	}

	public function start() {
		if ($this->processFactory->count() == 0) {
			return false;
		}

		$this->setDaemon();
		$this->setProcessName();

		if (SWOOLE_VERSION >= '4.4.0') {
			$manager = new PoolManager($this->processFactory->count(), $this->ipcType, $this->mqKey, true);
		} else {
			$manager = new PoolManager($this->processFactory->count(), $this->ipcType, $this->mqKey);
		}

		$listens = (new SwooleEvent())->getDefaultEvent()['process'];
		if ($this->ipcType == 0 || SWOOLE_VERSION >= '4.4.0') {
			unset($listens['message']);
		}
		foreach ($listens as $name => $class) {
			$object = \iloader()->get($class);
			$manager->on($name, function (PoolManager $pool, $data) use ($object) {
				$object->run($pool->getProcess(), $data, $this->processFactory, $this->mqKey);
			});
		}

		file_put_contents($this->pidFile, getmypid());
		$manager->start();
	}
}
