<?php

namespace W7\Core\Process\Pool;

use Swoole\Process;
use Swoole\Process\Pool as PoolManager;

/**
 * 该进程池由独立的process manager管理
 * Class IndependentPool
 * @package W7\Core\Process\Pool
 */
class IndependentPool extends PoolAbstract {
	private $ipcType = 0;
	private $pidFile;
	private $daemon;


	protected function init(){
		$this->ipcType = $this->config['ipc_type'] ?? 0;
		$this->pidFile = $this->config['pid_file'] ?? '/tmp/swoole_process_pool.pid';
		$this->daemon = $this->config['daemonize'] ?? false;
	}

	private function setDaemon() {
		if ($this->daemon) {
			Process::daemon(true, false);
		}
	}

	private function setProcessName() {
		isetProcessTitle('w7swoole_pool_master');
	}

	public function start() {
		$this->setDaemon();
		$this->setProcessName();

		if ($this->processFactory->count() == 0) {
			throw new \Exception('process num not be zero');
		}

		$manager = new PoolManager($this->processFactory->count(), $this->ipcType, $this->mqKey);

		$listens = iconfig()->getEvent()['process'];
		if ($this->ipcType == 0) {
			unset($listens['message']);
		}
		foreach ($listens as $name => $class) {
			$object = \iloader()->singleton($class);
			$manager->on($name, function (PoolManager $pool, $data) use ($object) {
				$object->run($pool->getProcess(), $data, $this->processFactory, $this->mqKey);
			});
		}

		file_put_contents($this->pidFile, getmypid());
		$manager->start();
	}

	public function stop() {
		if (!file_exists($this->pidFile)) {
			throw new \Exception('stop process server fail');
		}

		$pid = file_get_contents($this->pidFile);
		unlink($this->pidFile);
		return posix_kill($pid, SIGTERM);
	}
}