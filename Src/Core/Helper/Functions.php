<?php
/**
 * @author donknap
 * @date 18-7-19 上午10:49
 */

use Swoole\Coroutine;
use W7\App;
use W7\Core\Dispatcher\EventDispatcher;
use W7\Core\Dispatcher\ProcessDispather;
use W7\Core\Dispatcher\TaskDispatcher;

if (!function_exists('iprocess')) {
	function iprocess($name, $server = null)
	{
		/**
		 * @var ProcessDispather $dispatcher
		 */
		$dispatcher = iloader()->singleton(ProcessDispather::class);
		return $dispatcher->dispatch($name, $server);

	}
}
if (!function_exists("ievent")) {
	/**
	 * @param $eventName
	 * @param array $args
	 * @return bool
	 * @throws Exception
	 */
	function ievent($eventName, $args = [])
	{
		/**
		 * @var EventDispatcher $dispatcher
		 */
		$dispatcher = iloader()->singleton(EventDispatcher::class);
		return $dispatcher->dispatch($eventName, $args);
	}
}
if (!function_exists("itask")) {
	/**
	 * @param string $taskName
	 * @param array $params
	 * @param int $timeout
	 * @return false|int
	 * @throws \W7\Core\Exception\TaskException
	 */
	function itask($taskName, $params = [], int $timeout = 3) {
		/**
		 * @var TaskDispatcher $dispatcherMaker
		 */
		$dispatcherMaker = iloader()->singleton(TaskDispatcher::class);
		return $dispatcherMaker->register($taskName, 'run', $params, $timeout);
	}
}

if (!function_exists("iuuid")) {

	/**
	 * 获取UUID
	 * @return string
	 */
	function iuuid()
	{
		$len = rand(2, 16);
		$prefix = md5(substr(md5(Coroutine::getuid()), $len));
		return uniqid($prefix);
	}
}

if (!function_exists('iloader')) {
	/**
	 * 获取加载器
	 * @return \W7\Core\Helper\Loader
	 */
	function iloader()
	{
		return \W7\App::getLoader();
	}
}

if (!function_exists('istudly')) {
	function istudly($value)
	{
		$value = ucwords(str_replace(['-', '_'], ' ', $value));
		return str_replace(' ', '', $value);
	}
}

if (!function_exists('ioutputer')) {
	/**
	 * 获取输出对象
	 * @return W7\Console\Io\Output
	 */
	function ioutputer()
	{
		return iloader()->singleton(\W7\Console\Io\Output::class);
	}
}

if (!function_exists('iinputer')) {
	/**
	 * 输入对象
	 * @return W7\Console\Io\Input
	 */
	function iinputer()
	{
		return iloader()->singleton(\W7\Console\Io\Input::class);
	}
}

if (!function_exists('iconfig')) {
	/**
	 * 输入对象
	 * @return W7\Core\Config\Config
	 */
	function iconfig()
	{
		return iloader()->singleton(\W7\Core\Config\Config::class);
	}
}

if (!function_exists("ientity")) {
	function ientity($name)
	{
		$nameSpace = "W7\App\Model\Entity";
		if (class_exists($name)) {
			return iloader()->singleton($name);
		}
		if (class_exists($nameSpace . '\\' . $name)) {
			return iloader()->singleton($nameSpace . $name);
		}
		return false;
	}
}

if (!function_exists("ilogger")) {
	/**
	 * 返回logger对象
	 * @return \W7\Core\Log\Logger
	 */
	function ilogger()
	{
		return App::getLogger();
	}
}

if (!function_exists('isCo')) {
	/**
	 * 是否是在协成
	 * @return bool
	 */
	function isCo():bool
	{
		return Coroutine::getuid()>0;
	}
}

if (!function_exists("getClientIp")) {
	function getClientIp()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		return $_SERVER['REMOTE_ADDR'];
	}
}

if (!function_exists("isWorkerStatus")) {
	function isWorkerStatus() {
		if (App::$server === null) {
			return false;
		}

		$server = App::$server->getServer();
		if ($server->manager_pid == 0) {
			return false;
		}
		if ($server && \property_exists($server, 'taskworker') && ($server->taskworker === false)) {
			return true;
		}

		return false;
	}
}

if (!function_exists('isetProcessTitle')) {
	function isetProcessTitle($title) {
		if (\stripos(PHP_OS, 'Darwin') !== false) {
			return true;
		}
		if (\function_exists('cli_set_process_title')) {
			return cli_set_process_title($title);
		}

		if (\function_exists('swoole_set_process_name')) {
			return swoole_set_process_name($title);
		}
		return true;
	}
}