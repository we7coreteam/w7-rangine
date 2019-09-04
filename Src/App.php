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
use W7\Core\Helper\Loader;
use W7\Core\Log\Logger;
use W7\Core\Log\LogManager;
use W7\Core\Helper\Storage\Context;
use W7\Core\Provider\ProviderManager;
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
	 * @var Loader
	 */
	private $loader;

	public function __construct() {
		self::$self = $this;

		try {
			//初始化配置
			iconfig();
			$this->registerRuntimeEnv();
			$this->registerSecurityDir();
			$this->registerErrorHandler();
			$this->registerProvider();
		} catch (\Throwable $e) {
			ioutputer()->error($e->getMessage());
			exit();
		}
	}

	private function registerRuntimeEnv() {
		date_default_timezone_set('Asia/Shanghai');
	}

	private function registerSecurityDir() {
		//设置安全限制目录
		$openBaseDirConfig = iconfig()->getUserAppConfig('setting')['basedir'] ?? [];
		if (is_array($openBaseDirConfig)) {
			$openBaseDirConfig = implode(':', $openBaseDirConfig);
		}

		$openBaseDir = [
			'/tmp',
			sys_get_temp_dir(),
			APP_PATH,
			BASE_PATH . '/config',
			BASE_PATH . '/route',
			BASE_PATH . '/public',
			BASE_PATH . '/components',
			BASE_PATH . '/composer.json',
			RUNTIME_PATH,
			BASE_PATH . '/vendor',
			$openBaseDirConfig,
			session_save_path(),
			BASE_PATH . '/view'
		];
		ini_set('open_basedir', implode(':', $openBaseDir));
	}

	private function registerErrorHandler() {
		//设置了错误级别后只会收集错误级别内的日志, 容器确认后, 系统设置进行归类处理
		$setting = iconfig()->getUserAppConfig('setting');
		$errorLevel = $setting['error_reporting'] ?? ((ENV & RELEASE) === RELEASE ? E_ALL^E_NOTICE^E_WARNING : -1);
		error_reporting($errorLevel);

		/**
		 * 设置错误信息接管
		 */
		$this->getLoader()->singleton(HandlerExceptions::class)->registerErrorHandle();
	}

	private function registerProvider() {
		$this->getLoader()->singleton(ProviderManager::class)->register()->boot();
	}

	public static function getApp() {
		if (!self::$self) {
			new static();
		}
		return self::$self;
	}

	public function runConsole() {
		try {
			$this->getLoader()->singleton(Application::class)->run();
		} catch (\Throwable $e) {
			ioutputer()->error($e->getMessage());
		}
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
		/**
		 * @var LogManager $logManager
		 */
		$logManager = $this->getLoader()->singleton(LogManager::class);
		return $logManager->getDefaultChannel();
	}

	/**
	 * @return Context
	 */
	public function getContext() {
		return $this->getLoader()->singleton(Context::class);
	}

	public function getConfigger() {
		return $this->getLoader()->singleton(Config::class);
	}

	/**
	 * @return Cache
	 */
	public function getCacher() {
		/**
		 * @var Cache $cache;
		 */
		$cache = $this->getLoader()->singleton(Cache::class);
		return $cache;
	}
}
