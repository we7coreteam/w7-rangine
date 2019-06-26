<?php
/**
 * @author donknap
 * @date 18-11-22 下午8:27
 */

namespace W7\Core\Process;

use Swoole\Process;

abstract class ProcessAbstract {
	protected $name = 'process';
	protected $mqKey;
	protected $process;
	protected $interval = 1;

	public function __construct($name, Process $process = null) {
		$this->name = $name;
		$this->process = $process;

		$this->init();
	}

	protected function init() {

	}

	public function setProcess(Process $process) {
		$this->process = $process;
	}

	public function getProcess() {
		return $this->process;
	}

	/**
	 * process->push(msg) 有bug
	 * @param int $key
	 * @param int $mode
	 */
	public function setMq($key = 0, $mode = 2 | Process::IPC_NOWAIT) {
		$this->mqKey = $key;
		$this->process->useQueue($key, $mode);
	}

	protected function beforeStart() {}

	public function start() {
		if (\stripos(PHP_OS, 'Darwin') === false) {
			$this->process->name('w7swoole ' . $this->name . ' process');
		}

		/**
		 * 注册退出信号量,等本次业务执行完成后退出
		 */
		$runing = true;
		pcntl_signal(SIGTERM, function () use (&$runing) {
			$runing = false;
		});

		$this->beforeStart();

		while ($runing) {
			pcntl_signal_dispatch();
			try{
				$this->run();
			} catch (\Throwable $e) {
				ilogger()->error('run process fail with error ' . $e->getMessage());
			}

			sleep($this->interval);
		}
	}

	protected function run() {}

	public function exit($status=0) {
		$this->process->exit($status);
	}

	public function stop() {
		ilogger()->info('process ' . $this->name . ' exit');
	}
}