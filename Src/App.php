<?php
/**
 * @author donknap
 * @date 18-7-19 上午10:25
 */

namespace W7;

use W7\Core\Helper\Context;
use W7\Core\Log\Logger;
use W7\Core\Log\LogHelper;
use W7\Http\Server\Server;

class App
{
	const IA_ROOT = __DIR__;

	/**
	 * 服务器对象
	 *
	 * @var Server
	 */
	public static $server;
	/**
	 * @var Context $context
	 */
	public static $context;
	/**
	 * @var \W7\Core\Helper\Loader;
	 */
	private static $loader;
	private static $logger;

	public function __construct()
	{
		/**
		 * 设置错误信息接管
		 * @var LogHelper $logHanler
		 */
		$logHanler = iloader()->singleton(LogHelper::class);
		set_error_handler([$logHanler, 'errorHandler']);
	}

	public static function getLoader()
	{
		if (empty(self::$loader)) {
			self::$loader = new \W7\Core\Helper\Loader();
		}
		return self::$loader;
	}

	/**
	 * @return Logger
	 */
	public static function getLogger()
	{
		$defineConfig = iconfig()->getUserConfig('app');
		if (!empty(static::$logger) && static::$logger instanceof Logger) {
			return static::$logger;
		}

		/**
		 * @var Logger $logger
		 */
		static::$logger = iloader()->singleton(Logger::class);
		static::$logger->init($defineConfig['log']['log_file'], $defineConfig['log']['level'], $defineConfig['log']['flush_interval']);
		return static::$logger;
	}
}
