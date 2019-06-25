<?php

namespace W7\Core\Process\Pool;

use Swoole\Process\Pool as PoolManager;

class Pool {
	/**
	 * @var Container
	 */
	private $container;

	private $ipcType = 0;
	private $mqKey = 0;
	private $enableCoroutine = false;
	private $pidFile;
	private $daemonize;

	public function __construct($config) {
		$this->ipcType = $config['ipc_type'] ?? 0;
		$this->mqKey = $config['mq_key'] ?? 0;
		$this->enableCoroutine = $config['enable_coroutine'] ?? false;
		$this->pidFile = $config['pid_file'] ?? '/tmp/swoole_process_pool.pid';
		$this->daemonize = $config['daemonize'] ?? false;

		$this->container = iloader()->singleton(Container::class);
	}

	/**
	 * 保存添加的process
	 * 这里用普通变量保存的原因是 1:worker启动时所有的注册信息已全部保存. 2:worker重新启动时workerid是保持不变的
	 * @param $name
	 * @param $handle
	 * @param $num
	 */
	public function addProcess($name, $handle, $num) {
		$this->container->add($name, $handle, $num);
	}

	private function onWorkerStart(PoolManager $pool, $workerId) {
		$this->process = $this->container->make($workerId);
		$this->process->setProcess($pool->getProcess());

		if($this->mqKey) {
			$this->process->setMq($this->mqKey);
		}

		$this->process->start();

		if ($this->ipcType != 0) {
			$this->process->exit();
		}
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

	private function onMessage(PoolManager $pool, $message) {
		return $message;
	}

	public function start() {
		if ($this->daemonize) {
			$pid = pcntl_fork();
			if ($pid == -1) {
				throw new Exception('启动守护进程失败');
			}
			elseif ($pid > 0) {
				//父进程退出,子进程变成孤儿进程被1号进程收养，进程脱离终端
				exit(0);
			}
			// 让该进程脱离之前的会话，终端，进程组的控制
			posix_setsid();
			if ($this->container->count() == 0) {
				throw new \Exception('process num not be zero');
			}
		}

		file_put_contents($this->pidFile, getmypid());

		$manager = new PoolManager($this->container->count(), $this->ipcType, $this->mqKey);
		$manager->on('WorkerStart', function (PoolManager $pool, $workerId) {
			$this->onWorkerStart($pool, $workerId);
		});
		$manager->on('WorkerStop', function (PoolManager $pool, $workerId) {
			$this->onWorkerStop($pool, $workerId);
		});
		$this->ipcType !== 0 && $manager->on('message', function (PoolManager $pool, $message) {
			$this->onMessage($pool, $message);
		});

		$manager->start();
	}

	public function stop() {
		if (!file_exists($this->pidFile)) {
			throw new \Exception('stop crontab server fail');
		}

		$pid = file_get_contents($this->pidFile);
		unlink($this->pidFile);
		posix_kill($pid, SIGTERM);
	}
}