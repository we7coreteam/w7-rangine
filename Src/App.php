<?php
/**
 * @author donknap
 * @date 18-7-19 上午10:25
 */

namespace W7;

use W7\Console\Application;
use W7\Core\Cache\Cache;
use W7\Core\Config\Config;
use W7\Core\Container\Container;
use W7\Core\Container\Context;
use W7\Core\Log\Logger;
use W7\Core\Log\LogManager;
use W7\Core\Provider\ProviderManager;
use W7\Http\Server\Server;

class App {
	/**
	 * @var App
	 */
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

	/**
	 * @var ProviderManager
	 */
	private $providerManager;

	public function __construct() {
		$this->init();
	}

	private function init() {
		static::$self = $this;
		$this->container = new Container();
		$this->getConfigger();
		$this->providerManager = iloader()->singleton(ProviderManager::class);
	}

	private function register() {
		$this->providerManager->register();
	}

	private function boot() {
		$this->providerManager->boot();
	}

	public function runConsole() {
		try{
			$this->register();
			$this->boot();
			(new Application())->run();
		} catch (\Throwable $e) {
			ioutputer()->error($e->getMessage());
		}
	}

	public static function getApp() {
		return self::$self;
	}

	public function getLoader() {
		return $this->container;
	}

	/**
	 * @return Logger
	 */
	public function getLogger() {
		/**
		 * @var LogManager $logManager
		 */
		$logManager = $this->container->get(LogManager::class);
		return $logManager->getDefaultChannel();
	}

	/**
	 * @return Context
	 */
	public function getContext() {
		return $this->container->get(Context::class);
	}

	public function getConfigger() {
		return $this->container->get(Config::class);
	}

	/**
	 * @return Cache
	 */
	public function getCacher() {
		/**
		 * @var Cache $cache;
		 */
		return $this->container->get(Cache::class);
	}
}
