<?php
/**
 * author: alex
 * date: 18-8-3 上午10:46
 */

namespace W7\Core\Dispatcher;

use W7\Core\Process\ProcessAbstract;

class ProcessDispather extends DispatcherAbstract {

	/**
	 * @var array
	 */
	private static $processes = [];

	public function register() {

	}

	public function dispatch(...$params) {
		$name = $params[0];
		$server = $params[1];

		if (isset(self::$processes[$name])) {
			return self::$processes[$name];
		}

		if (!class_exists($name)) {
			ilogger()->warning(sprintf("Process is worng name is %s", $name));
			return false;
		}
		/**
		 * @var ProcessAbstract $process
		 */
		$process = iloader()->singleton($name);
		$checkInfo = call_user_func([$process, "check"]);
		if (!$checkInfo) {
			return false;
		}

		/**
		 * @var \swoole_process $swooleProcess
		 */
		$swooleProcess = new \swoole_process(function (\swoole_process $worker) use ($process, $name) {
			$worker->name('w7swoole ' . $name . '-' . $worker->pipe . ' process');
			$process->run($worker);
			//如果进程包含read方法，自动添加事件侦听，获取主进程发送的消息
			if (method_exists($process, 'read')) {
				//增加事件循环，将消息接收到类中
				swoole_event_add($worker->pipe, function($pipe) use ($worker, $process) {
					$recv = $worker->read();
					if (!$process->read($worker, $recv)) {
						swoole_event_del($worker->pipe);
					}
					sleep($process->readInterval);
				});
			}

		}, false, SOCK_DGRAM);

		self::$processes[$name] = $swooleProcess;
		if (!empty($server)) {
			$server->addProcess($swooleProcess);
		} else {
			$swooleProcess->useQueue();
			$swooleProcess->start();
		}

		return $swooleProcess;
	}

	/**
	 * 发送信息到一个进程内，进程内需要实现read方法来接收
	 * @param $name
	 * @param $data
	 */
	public function write($name, $data) {
		/**
		 * @var \swoole_process $process
		 */
		$process = self::$processes[$name];
		if (empty($process)) {
			throw new \RuntimeException('Process not exists');
		}

		$process->write($data);
	}

	public function getProcess($name) {
		/**
		 * @var \swoole_process $process
		 */
		$process = self::$processes[$name];
		if (empty($process)) {
			throw new \RuntimeException('Process not exists');
		}

		return $process;
	}
}
