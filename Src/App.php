<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7;

use W7\Console\Application;
use W7\Core\Cache\Cache;
use W7\Core\Config\Config;
use W7\Core\Exception\HandlerExceptions;
use W7\Core\Container\Container;
use W7\Core\Log\Logger;
use W7\Core\Log\LogManager;
use W7\Core\Helper\Storage\Context;
use W7\Core\Provider\ProviderManager;
use W7\Http\Server\Server;

class App {
	const NAME = 'w7-rangine';
	const VERSION = '2.3.0';

	private static $self;
	/**
	 * 服务器对象
	 *
	 * @var Server
	 */
	public static $server;
	/**
	 * @var Container
	 */
	private $container;

	public function __construct() {
		self::$self = $this;

		//初始化配置
		iconfig()->load();
		$this->registerRuntimeEnv();
		$this->registerErrorHandler();
		$this->registerProvider();
		$this->registerSecurityDir();
	}

	private function registerRuntimeEnv() {
		$defaultTimezone = iconfig()->get('app.setting.timezone', 'Asia/Shanghai');
		date_default_timezone_set($defaultTimezone);

		if (!is_dir(RUNTIME_PATH)) {
			mkdir(RUNTIME_PATH, 0777, true);
		}
		if (!is_readable(RUNTIME_PATH)) {
			throw new \RuntimeException('path ' . RUNTIME_PATH . ' no read permission');
		}
		if (!is_writeable(RUNTIME_PATH)) {
			throw new \RuntimeException('path ' . RUNTIME_PATH . ' no write permission');
		}

		$env = $this->getConfigger()->get('app.setting.env', DEVELOPMENT);
		!defined('ENV') && define('ENV', $env);
		if (!is_numeric(ENV) || ((RELEASE|DEVELOPMENT) & ENV) !== ENV) {
			throw new \RuntimeException("config setting['env'] error, please use the constant RELEASE, DEVELOPMENT, DEBUG, CLEAR_LOG, BACKTRACE instead");
		}
	}

	private function registerSecurityDir() {
		//设置安全限制目录
		$openBaseDirConfig = iconfig()->get('app.setting.basedir', []);
		if (is_array($openBaseDirConfig)) {
			$openBaseDirConfig = implode(':', $openBaseDirConfig);
		}

		$openBaseDir = [
			'/tmp',
			sys_get_temp_dir(),
			APP_PATH,
			BASE_PATH . '/bootstrap',
			BASE_PATH . '/config',
			BASE_PATH . '/route',
			BASE_PATH . '/public',
			BASE_PATH . '/components',
			BASE_PATH . '/composer.json',
			RUNTIME_PATH,
			BASE_PATH . '/vendor',
			$openBaseDirConfig,
			session_save_path()
		];
		ini_set('open_basedir', implode(':', $openBaseDir));
	}

	private function registerErrorHandler() {
		//设置了错误级别后只会收集错误级别内的日志, 容器确认后, 系统设置进行归类处理
		$setting = iconfig()->get('app.setting');
		$errorLevel = $setting['error_reporting'] ?? ((ENV & RELEASE) === RELEASE ? E_ALL^E_NOTICE^E_WARNING : -1);
		error_reporting($errorLevel);

		((ENV & DEBUG) === DEBUG) && ini_set('display_errors', 'On');

		/**
		 * 设置错误信息接管
		 */
		$this->getContainer()->get(HandlerExceptions::class)->registerErrorHandle();
	}

	private function registerProvider() {
		$this->getContainer()->get(ProviderManager::class)->register()->boot();
	}

	public static function getApp() {
		if (!self::$self) {
			new static();
		}
		return self::$self;
	}

	public function runConsole() {
		try {
			$this->getContainer()->get(Application::class)->run();
		} catch (\Throwable $e) {
			ioutputer()->error($e->getMessage());
		}
	}

	public function getContainer() {
		if (empty($this->container)) {
			$this->container = new Container();
		}
		return $this->container;
	}

	/**
	 * @return Logger
	 */
	public function getLogger() {
		/**
		 * @var LogManager $logManager
		 */
		$logManager = $this->getContainer()->get(LogManager::class);
		return $logManager->getDefaultChannel();
	}

	/**
	 * @return Context
	 */
	public function getContext() {
		return $this->getContainer()->get(Context::class);
	}

	public function getConfigger() {
		return $this->getContainer()->get(Config::class);
	}

	/**
	 * @return Cache
	 */
	public function getCacher() {
		/**
		 * @var Cache $cache;
		 */
		$cache = $this->getContainer()->get(Cache::class);
		return $cache;
	}

	public function bootstrapCachePath($path = '') {
		return BASE_PATH . DIRECTORY_SEPARATOR . 'bootstrap/cache' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
	}

	public function getRouteCachePath() {
		return $this->bootstrapCachePath('route/');
	}

	public function getConfigCachePath() {
		return $this->bootstrapCachePath('config/');
	}

	public function exit() {
		$this->container->clear();
	}
}
