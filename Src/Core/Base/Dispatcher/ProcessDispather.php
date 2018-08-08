<?php
/**
 * author: alex
 * date: 18-8-3 上午10:46
 */

namespace W7\Core\Base\Dispatcher;

use Swoole\Process as SwooleProcess;

class ProcessDispather extends DispatcherFacade
{

	/**
	 * @var array
	 */
	private static $processes = [];

	/**
	 * @param mixed ...$param
	 * @return bool|mixed|SwooleProcess
	 */
	public function build(...$param)
	{
		$name = $param[0];
		$server = $param[1];
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
		 * @var SwooleProcess $swooleProcess
		 */

		$swooleProcess = new SwooleProcess(function (SwooleProcess $swooleProcess) use ($server, $name) {
			$processe = iloader()->singleton($name);
			$processe->run($swooleProcess);
		});
		$swooleProcess->useQueue();
		$swooleProcess->start();
		self::$processes[$name] = $swooleProcess;

		return $swooleProcess;
	}
}
