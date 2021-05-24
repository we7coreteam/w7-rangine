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

namespace W7\Core\Provider;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use W7\App;
use W7\Console\Application;
use W7\Contract\Config\RepositoryInterface;
use W7\Contract\Logger\LoggerFactoryInterface;
use W7\Contract\Router\RouterInterface;
use W7\Contract\View\ViewInterface;
use W7\Core\Container\Container;
use W7\Core\Helper\Traiter\AppCommonTrait;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;

/**
 * Class ProviderAbstract
 * @package W7\Core\Provider
 * @property-read Container $container
 * @property-read RepositoryInterface $config
 * @property-read LoggerFactoryInterface $logger
 * @property-read RouterInterface $router
 */
abstract class ProviderAbstract {
	use AppCommonTrait;

	protected $name;
	protected $packageName;
	protected $packageNamespace;

	public static $publishes = [];
	public static $publishGroups = [];
	protected $rootPath;

	final public function __construct($name = null) {
		if (!$name) {
			$name = get_called_class();
		}
		$this->name = $name;
		if ($this->packageName) {
			$this->rootPath = App::getApp()->getBasePath() . '/vendor/' . $this->packageName;
			!$this->packageNamespace && $this->packageNamespace = str_replace('/', '\\', Str::studly($this->packageName));
		} else {
			$reflect = new \ReflectionClass($this);
			$this->packageNamespace = $reflect->getNamespaceName();
			$this->rootPath = dirname($reflect->getFileName(), 2);
		}
	}

	/**
	 * Register any application services.
	 * @return void
	 */
	public function register() {
	}

	/**
	 * boot any application services
	 * @return mixed
	 */
	public function boot() {
	}

	protected function registerOpenBaseDir($dir) {
		$dir = (array)$dir;
		$appBasedir = $this->getConfig()->get('app.setting.basedir', []);
		$appBasedir = array_merge($appBasedir, $dir);
		$this->getConfig()->set('app.setting.basedir', $appBasedir);
	}

	protected function registerProvider($provider) {
		$this->getContainer()->get(ProviderManager::class)->registerProvider($provider);
	}

	protected function registerConfig($fileName, $key) {
		$this->mergeConfigFrom($this->rootPath . '/config/' . $fileName, $key);
	}

	protected function publishConfig($sourceFileName, $targetFileName = null, $group = null) {
		if (!isCli()) {
			return false;
		}

		if (!$targetFileName) {
			$targetFileName = $sourceFileName;
		}
		$this->publishes([
			$this->rootPath . '/config/' . $sourceFileName => App::getApp()->getBasePath() . '/config/' . $targetFileName
		], $group);
	}

	protected function registerLogger($channel, $config = []) {
		$logger = $this->logger->createLogger($channel, $config);
		$this->logger->registerLogger($channel, $logger);
	}

	protected function registerRoute($fileName, $options = []) {
		if (App::getApp()->routeIsCached()) {
			return true;
		}
		$routeConfig = [
			'namespace' => $this->packageNamespace,
			'module' => $this->name,
			'name' => $this->name
		];
		$routeConfig = array_merge($routeConfig, $options);

		$this->getRouter()->name($routeConfig['name'])->middleware($routeConfig['middleware'] ?? [])->group($routeConfig, function () use ($fileName) {
			$this->loadRouteFrom($this->rootPath . '/route/' . $fileName);
		});
	}

	protected function registerStaticResource() {
		$documentRoot = $this->getConfig()->get('server.common.document_root');
		if (!$documentRoot) {
			throw new \RuntimeException("please set server['common']['document_root']");
		}

		$filesystem = new Filesystem();
		$documentRoot = rtrim($documentRoot, '/');
		$flagFilePath = $documentRoot . '/' . $this->name . '/resource.lock';
		if ($filesystem->exists($this->rootPath . '/resource') && !$filesystem->exists($flagFilePath)) {
			$filesystem->copyDirectory($this->rootPath . '/resource', $documentRoot . '/' . $this->name);
			$filesystem->put($flagFilePath, '');
		}
	}

	protected function registerView($namespace) {
		$this->getView()->addTemplatePath($namespace, $this->rootPath . '/view/');
	}

	protected function registerCommand($namespace = '') {
		if (!isCli()) {
			return false;
		}
		/**
		 * @var  Application $application
		 */
		$application = $this->getContainer()->get(Application::class);
		$application->autoRegisterCommands($this->rootPath . '/src/Command', $this->packageNamespace, $namespace);
	}

	protected function registerServer($name, $class) {
		ServerEnum::registerServer($name, $class);
	}

	protected function registerServerEvent($name, array $events, $cover = false) {
		/**
		 * @var ServerEvent $event
		 */
		$event = $this->getContainer()->get(ServerEvent::class);
		$event->addServerEvents($name, $events, $cover);
	}

	protected function setRootPath($path) {
		$this->rootPath = $path;
	}

	/**
	 * Merge the given configuration with the existing configuration.
	 * @param $path
	 * @param $key
	 */
	protected function mergeConfigFrom($path, $key) {
		if (App::getApp()->configurationIsCached()) {
			return true;
		}
		$config = $this->getConfig()->get($key, []);
		$this->getConfig()->set($key, array_merge(require $path, $config));
	}

	/**
	 * Load the given routes file
	 * Configuration file routing configuration is not supported
	 * @param $path
	 */
	protected function loadRouteFrom($path) {
		if (App::getApp()->routeIsCached()) {
			return true;
		}
		include $path;
	}

	/**
	 * Register paths to be published by the publish command.
	 * @param $paths
	 * @param null $groups
	 */
	protected function publishes($paths, $groups = null) {
		$this->ensurePublishArrayInitialized($class = static::class);

		static::$publishes[$class] = array_merge(static::$publishes[$class], $paths);

		if (! is_null($groups)) {
			foreach ((array) $groups as $group) {
				$this->addPublishGroup($group, $paths);
			}
		}
	}

	/**
	 * Ensure the publish array for the service provider is initialized.
	 *
	 * @param  string  $class
	 * @return void
	 */
	protected function ensurePublishArrayInitialized($class) {
		if (! array_key_exists($class, static::$publishes)) {
			static::$publishes[$class] = [];
		}
	}

	/**
	 * Add a publish group / tag to the service provider.
	 *
	 * @param  string  $group
	 * @param  array  $paths
	 * @return void
	 */
	protected function addPublishGroup($group, $paths) {
		if (! array_key_exists($group, static::$publishGroups)) {
			static::$publishGroups[$group] = [];
		}

		static::$publishGroups[$group] = array_merge(
			static::$publishGroups[$group],
			$paths
		);
	}

	/**
	 * Get the paths to publish.
	 *
	 * @param  string  $provider
	 * @param  string  $group
	 * @return array
	 */
	public static function pathsToPublish($provider = null, $group = null) {
		if (! is_null($paths = static::pathsForProviderOrGroup($provider, $group))) {
			return $paths;
		}
		return [];
	}

	/**
	 * Get the paths for the provider or group (or both).
	 *
	 * @param  string|null  $provider
	 * @param  string|null  $group
	 * @return array
	 */
	protected static function pathsForProviderOrGroup($provider, $group) {
		if ($provider && $group) {
			return static::pathsForProviderAndGroup($provider, $group);
		} elseif ($group && array_key_exists($group, static::$publishGroups)) {
			return static::$publishGroups[$group];
		} elseif ($provider && array_key_exists($provider, static::$publishes)) {
			return static::$publishes[$provider];
		} elseif ($group || $provider) {
			return [];
		}
	}

	/**
	 * Get the paths for the provider and group.
	 *
	 * @param  string  $provider
	 * @param  string  $group
	 * @return array
	 */
	protected static function pathsForProviderAndGroup($provider, $group) {
		if (! empty(static::$publishes[$provider]) && ! empty(static::$publishGroups[$group])) {
			return array_intersect_key(static::$publishes[$provider], static::$publishGroups[$group]);
		}
		return [];
	}

	protected function getRouter() : RouterInterface {
		return $this->container->get(RouterInterface::class);
	}

	protected function getView() : ViewInterface {
		return $this->container->get(ViewInterface::class);
	}

	public function __get($name) {
		$method = 'get' . ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method();
		}

		throw new \RuntimeException('property ' . $name . ' not exists');
	}

	public function providers() : array {
		return [];
	}
}
