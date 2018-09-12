<?php
/**
 * @author donknap
 * @date 18-7-19 上午10:25
 */

namespace W7;

use W7\Console\Console;
use W7\Core\Helper\Context;
use W7\Core\Helper\Loader;
use W7\Core\Log\Logger;
use W7\Core\Log\LogHelper;
use W7\Http\Server\Server;

class App {
	private static $self;
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
	 * @var Loader
	 */
	private $loader;
	/**
	 * @var Logger
	 */
	private $logger;

	public static function getApp() {
		if (!empty(self::$self)) {
			return self::$self;
		}
		self::$self = new static();
		self::$self->setErrorHandler();

		return self::$self;
	}

	public function setErrorHandler() {
		/**
		 * 设置错误信息接管
		 * @var LogHelper $logHanler
		 */
		$logHanler = iloader()->singleton(LogHelper::class);
		set_error_handler([$logHanler, 'errorHandler']);
		set_exception_handler($logHanler, 'exceptionHandler');
	}

	public function runConsole() {
		/**
		 * @var Console $console
		 */
		$console = iloader()->singleton(Console::class);
		$console->run();

	}

	public function getLoader() {
		if (empty($this->loader)) {
			$this->loader = new Loader();
		}
		return $this->loader;
	}

	/**
	 * @return Logger
	 */
	public function getLogger() {
		$defineConfig = iconfig()->getUserConfig('app');
		if (empty($this->logger)) {
			$this->logger = iloader()->singleton(Logger::class);
			$this->logger->init($defineConfig['log']['log_file'], $defineConfig['log']['level'], $defineConfig['log']['flush_interval']);
		}
		return $this->logger;
	}
}
