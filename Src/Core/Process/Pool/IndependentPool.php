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
		$manager->on('WorkerStart', function (PoolManager $pool, $workerId) {
			$this->onWorkerStart($pool, $workerId);
		});
		$manager->on('WorkerStop', function (PoolManager $pool, $workerId) {
			$this->onWorkerStop($pool, $workerId);
		});
		$this->ipcType !== 0 && $manager->on('message', function (PoolManager $pool, $message) {
			$this->onMessage($pool, $message);
		});

		file_put_contents($this->pidFile, getmypid());
		$manager->start();
	}

	private function onWorkerStart(PoolManager $pool, $workerId) {
		$this->process = $this->processFactory->make($workerId);
		$this->process->setProcess($pool->getProcess());

		if($this->mqKey) {
			$this->process->setMq($this->mqKey);
		}

		$this->process->start();
	}

	private function onWorkerStop(PoolManager $pool, $workerId) {
		if (empty($this->process)) {
			return false;
		}

		try{
			$this->process->stop();
		} catch (\Throwable $e) {
			ilogger()->error('stop process fail with error ' . $e->getMessage());
		}
	}

	/**
	 * 该方式暂时不启用
	 * @param PoolManager $pool
	 * @param $message
	 * @return mixed
	 */
	private function onMessage(PoolManager $pool, $message) {
		return $message;
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