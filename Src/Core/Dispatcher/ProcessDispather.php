<?php
/**
 * author: alex
 * date: 18-8-3 上午10:46
 */

namespace W7\Core\Dispatcher;

class ProcessDispather extends DispatcherAbstract
{

	/**
	 * @var array
	 */
	private static $processes = [];

	public function register()
	{

	}

	public function dispatch(...$params)
	{
		$name = $params[0];
		$server = $params[1];

		if (isset(self::$processes[$name])) {
			return self::$processes[$name];
		}

		if (!class_exists($name)) {
			ilogger()->warning("Process is worng name is %s", $name);
			return false;
		}

		$process = iloader()->singleton($name);
		$checkInfo = call_user_func([$process, "check"]);
		if (!$checkInfo) {
			return false;
		}

		/**
		 * @var \swoole_process $swooleProcess
		 */
		$swooleProcess = new \swoole_process(function (\swoole_process $worker) use ($process) {
			$process->run($worker);
		});
		$swooleProcess->name('w7swoole ' . $name . ' process');
		self::$processes[$name] = $swooleProcess;

		if (!empty($server)) {
			$server->server->addProcess($swooleProcess);
		} else {
			$swooleProcess->useQueue();
			$swooleProcess->start();
		}
		return $swooleProcess;
	}
}
