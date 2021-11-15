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
use W7\Console\Io\Output;
use W7\Contract\Config\RepositoryInterface;
use W7\Core\Bootstrap\BootstrapInterface;
use W7\Core\Bootstrap\LoadConfigBootstrap;
use W7\Core\Bootstrap\ProviderBootstrap;
use W7\Core\Bootstrap\RegisterHandleExceptionsBootstrap;
use W7\Core\Bootstrap\RegisterRuntimeEnvBootstrap;
use W7\Core\Bootstrap\RegisterSecurityDirBootstrap;
use W7\Core\Container\Container;
use W7\Core\Server\ServerAbstract;

/**
 * Class App
 * @package W7
 */
class App {
	public const NAME = 'w7-rangine';
	public const VERSION = '2.5.6';

	public static App $self;
	protected string $appNamespace;
	/**
	 * 服务器对象
	 *
	 * @var ServerAbstract
	 */
	public static ServerAbstract $server;
	/**
	 * @var Container
	 */
	protected Container $container;

	protected array $bootstrapMap = [
		LoadConfigBootstrap::class,
		RegisterRuntimeEnvBootstrap::class,
		RegisterHandleExceptionsBootstrap::class,
		ProviderBootstrap::class,
		RegisterSecurityDirBootstrap::class
	];

	public function __construct() {
		self::$self = $this;

		$this->bootstrap();
	}

	protected function bootstrap(): void {
		foreach ($this->bootstrapMap as $bootstrap) {
			/**
			 * @var BootstrapInterface $bootstrap
			 */
			$bootstrap = new $bootstrap();
			$bootstrap->bootstrap($this);
		}
	}

	public static function getApp(): App {
		if (!self::$self) {
			new static();
		}
		return self::$self;
	}

	public function getAppNamespace(): string {
		if (!is_null($this->appNamespace)) {
			return $this->appNamespace;
		}

		if (defined('APP_NAMESPACE')) {
			return $this->appNamespace = APP_NAMESPACE;
		}

		return $this->appNamespace = 'W7\\App';
	}

	public function runConsole(): void {
		try {
			$this->getContainer()->get(Application::class)->run();
		} catch (\Throwable $e) {
			(new Output())->error($e->getMessage());
		}
	}

	public function getContainer(): Container {
		if (empty($this->container)) {
			$this->container = new Container();
		}
		return $this->container;
	}

	public function getConfigger(): RepositoryInterface {
		return $this->getContainer()->get(RepositoryInterface::class);
	}

	public function getAppPath() {
		return APP_PATH;
	}

	public function getBasePath(): string {
		return BASE_PATH;
	}

	public function getRuntimePath(): string {
		return RUNTIME_PATH;
	}

	public function bootstrapCachePath($path = ''): string  {
		return $this->getBasePath() . '/bootstrap/cache' . ($path ? ('/' . $path) : $path);
	}

	public function getRouteCachePath(): string {
		return $this->bootstrapCachePath('route/');
	}

	public function getConfigCachePath(): string {
		return $this->bootstrapCachePath('config/');
	}

	public function configurationIsCached(): bool {
		return is_dir($this->getConfigCachePath());
	}

	public function getBuiltInConfigPath(): string  {
		return $this->getBasePath() . '/vendor/composer/rangine/autoload/config';
	}

	public function routeIsCached(): bool {
		return is_dir($this->getRouteCachePath());
	}

	public function exit(): void {
		$this->container->clear();
	}
}
