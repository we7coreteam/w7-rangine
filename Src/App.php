<?php
/**
 * @author donknap
 * @date 18-7-19 上午10:25
 */

namespace W7;

use W7\Console\Application;
use W7\Core\Cache\Cache;
use W7\Core\Config\Config;
use W7\Core\Config\Env;
use W7\Core\Container\Container;
use W7\Core\Container\Context;
use W7\Core\Log\Logger;
use W7\Core\Log\LogManager;
use W7\Core\Provider\ProviderManager;
use W7\Http\Server\Server;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

class App {
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
		$this->init();
		$this->registerErrorHandle();
	}

	private function init() {
		$this->initContainer();
		$this->initEnv();
		$this->initConfig();
		static::$self = $this;
	}

	private function initContainer() {
		$this->container = new Container();
	}

	private function initEnv() {
		(new Env(BASE_PATH))->load();
	}

	private function initConfig() {
		$this->container->get(Config::class);
	}

	private function bootProviders() {
		$this->container->get(ProviderManager::class)->register()->boot();
	}

	private function registerErrorHandle() {
		$processer = new Run();
		$handle = new PlainTextHandler($this->getLogger());
		$processer->pushHandler($handle);
		$processer->register();
	}

	public function runConsole() {
		$this->bootProviders();
		(new Application())->run();
	}

	public static function getApp() {
		return self::$self;
	}

	/**
	 * @return Container
	 */
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