<?php

/**
 * WeEngine Api System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Core\Process;

use Swoole\Process;
use Swoole\Timer;
use W7\Core\Log\LogManager;

abstract class ProcessAbstract {
	protected $name = 'process';
	protected $num = 1;
	protected $mqKey;
	/**
	 * @var Process
	 */
	protected $process;
	// event 模式下支持用户自定义pipe
	protected $pipe;

	//定时器模式下
	protected $runTimer;
	protected $interval = 1;
	private $exitTimer;
	private $complete;
	private $exitStatus;

	public function __construct($name, $num = 1, Process $process = null) {
		$this->name = $name;
		$this->num = $num;
		$this->process = $process;

		$this->init();
	}

	protected function init() {
	}

	public function getName() {
		return $this->name;
	}

	public function setProcess(Process $process) {
		$this->process = $process;
	}

	public function getProcess() {
		return $this->process;
	}

	private function getProcessName() {
		$name = 'w7-rangine ' . $this->name;
		if ($this->num > 1) {
			$name .= '-' . ($this->process->id % $this->num);
		}

		return $name;
	}

	/**
	 * process->push(msg) 有bug
	 * 默认的消息队列消费方式为争抢方式
	 * @param int $key
	 * @param int $mode
	 */
	public function setMq($key = 0, $mode = 2 | Process::IPC_NOWAIT) {
		$this->mqKey = $key;
		$this->process->useQueue($key, $mode);
	}

	public function check() {
		return true;
	}

	protected function beforeStart() {
	}

	public function onStart() {
		if (\stripos(PHP_OS, 'Darwin') === false) {
			$this->process->name($this->getProcessName());
		}

		/**
		 * 注册退出信号量,等本次业务执行完成后退出,在执行stop后需要等待sleep结束后再结束
		 */
		$this->exitStatus = 2;
		$this->complete = true;
		pcntl_signal(SIGTERM, function () {
			--$this->exitStatus;
		});

		$this->beforeStart();

		if (method_exists($this, 'read')) {
			$this->startByEvent();
		} else {
			$this->startByTimer();
		}

		$this->exitTimer = Timer::tick(1000, function ($timer) {
			pcntl_signal_dispatch();
			/**
			 * 得到退出信号,但是任务定时器正在等待下一个时间点的时候,强制clear time,退出当前进程
			 */
			if ($this->exitStatus === 1 && $this->complete) {
				$this->stop();
			}
		});
	}

	private function startByTimer() {
		$this->runTimer = Timer::tick($this->interval * 1000, function ($timer) {
			$this->doRun(function () {
				$this->run();
			});
		});
	}

	private function startByEvent() {
		$pipe = $this->pipe ? $this->pipe : $this->process->pipe;
		swoole_event_add($pipe, function ($fd) {
			$this->doRun(function () {
				$data = $this->pipe ? '' : $this->process->read();
				$this->read($data);
			});
		});
	}

	private function doRun(\Closure $callback) {
		$this->complete = false;
		try {
			$callback();
		} catch (\Throwable $e) {
			ilogger()->error('run process fail with error ' . $e->getMessage());
		}
		$this->complete = true;

		//如果在执行完成后就得到退出信息,则马上退出
		if ($this->exitStatus === 1) {
			$this->stop();
		}
	}

	protected function run() {
	}

	public function stop() {
		--$this->exitStatus;
		if ($this->runTimer) {
			Timer::clear($this->runTimer);
			$this->runTimer = null;
		}
		if ($this->exitTimer) {
			Timer::clear($this->exitTimer);
			$this->exitTimer = null;
		}
		if (method_exists($this, 'read')) {
			swoole_event_del($this->pipe ? $this->pipe : $this->process->pipe);
		}

		$this->process->kill($this->process->pid);
	}

	public function sendMsg($msg) {
		//swoole 版本不兼容, 不能用push
		return msg_send(msg_get_queue($this->mqKey), 1, $msg, false);
	}

	public function getMsg($size = null) {
		return $this->getProcess()->pop($size);
	}

	public function onStop() {
		ilogger()->info('process ' . $this->getProcessName() . ' exit');
		iloader()->get(LogManager::class)->flushLog();
	}
}
