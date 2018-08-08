<?php
/**
 * @author donknap
 * @date 18-7-19 上午10:49
 */

use Swoole\Coroutine;
use W7\App;
use W7\Core\Base\Dispatcher\EventDispatcher;
use W7\Core\Base\Dispatcher\ProcessDispather;
use W7\Core\Base\Dispatcher\TaskDispatcher;
use W7\Core\Helper\Log\Logger;
use W7\Core\Helper\StringHelper;

if (!function_exists('iprocess')) {
	function iprocess($name, $server)
	{
		/**
		 * @var ProcessDispather $dispatcherMaker
		 */
		$dispatcherMaker = iloader()->singleton(ProcessDispather::class);
		try {
			$process = $dispatcherMaker->build($name, $server);
		} catch (Throwable $throwable) {
			ilogger()->warning($throwable->getMessage());
			return false;
		}
		return $process;
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
		 * @var EventDispatcher $dispatcherMaker
		 */
		$dispatcherMaker = iloader()->singleton(EventDispatcher::class);
		return $dispatcherMaker->trigger($eventName, $args);
	}
}
if (!function_exists("itask")) {
	/**
	 * @param string $taskName
	 * @param string $methodName
	 * @param array $params
	 * @param int $timeout
	 * @return false|int
	 * @throws \W7\Core\Exception\TaskException
	 */
	function itask(string $taskName, string $methodName, array $params = [], int $timeout = 3)
	{
		/**
		 * @var TaskDispatcher $dispatcherMaker
		 */
		$dispatcherMaker = iloader()->singleton(TaskDispatcher::class);
		return $dispatcherMaker->register($taskName, $methodName, $params, $timeout);
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

if (!function_exists('getApp')) {
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
	 * @return Logger
	 */
	function ilogger()
	{
		return App::getLogger();
	}
}

if (!function_exists('isMac')) {
	/**
	 * 是否是mac环境
	 *
	 * @return bool
	 */
	function isMac(): bool
	{
		return \stripos(PHP_OS, 'Darwin') !== false;
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
	function isWorkerStatus()
	{
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
if (! function_exists('ienv')) {
	/**
	 *获取ENV的参数.
	 *
	 * @param  string $key
	 * @param  mixed $default
	 * @return mixed
	 */
	function ienv($key, $default = null)
	{
		$value = getenv($key);

		if ($value === false) {
			return value($default);
		}

		switch (strtolower($value)) {
			case 'true':
			case '(true)':
				return true;
			case 'false':
			case '(false)':
				return false;
			case 'empty':
			case '(empty)':
				return '';
			case 'null':
			case '(null)':
				return;
		}

		if (strlen($value) > 1 && StringHelper::startsWith($value, '"') && StringHelper::endsWith($value, '"')) {
			return substr($value, 1, -1);
		}

		if (defined($value)) {
			$value = constant($value);
		}

		return $value;
	}
}
