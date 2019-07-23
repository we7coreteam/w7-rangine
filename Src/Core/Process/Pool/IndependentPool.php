<?php

namespace W7\Core\Process\Pool;

use Swoole\Process\Pool as PoolManager;

/**
 * 该进程池由独立的process manager管理
 * Class IndependentPool
 * @package W7\Core\Process\Pool
 */
class IndependentPool extends PoolAbstract {
	private $ipcType = 0;
	private $pidFile;
	private $daemonize;


	protected function init(){
		$this->ipcType = $this->config['ipc_type'] ?? 0;
		$this->pidFile = $this->config['pid_file'] ?? '/tmp/swoole_process_pool.pid';
		$this->daemonize = $this->config['daemonize'] ?? false;
	}

	public function start() {
		if ($this->daemonize) {
			$pid = pcntl_fork();
			if ($pid == -1) {
				throw new \Exception('启动守护进程失败');
			}
			elseif ($pid > 0) {
				//父进程退出,子进程变成孤儿进程被1号进程收养，进程脱离终端
				exit(0);
			}
			// 让该进程脱离之前的会话，终端，进程组的控制
			posix_setsid();
		}
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