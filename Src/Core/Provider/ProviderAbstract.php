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
use W7\Console\Application;
use W7\Core\Helper\StringHelper;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Core\View\View;

abstract class ProviderAbstract {
	protected $name;

	//composer包名
	protected $packageName;
	//composer包的namespace
	protected $packageNamespace;

	/**
	 * @var \W7\Core\Config\Config
	 */
	protected $config;
	/**
	 * @var \W7\Core\Route\Route
	 */
	protected $router;
	/**
	 * @var \W7\Core\Log\Logger
	 */
	protected $logger;

	protected $defer;
	public static $publishes = [];
	public static $publishGroups = [];
	protected $rootPath;

	public function __construct($name = null) {
		if (!$name) {
			$name = get_called_class();
		}
		$this->name = $name;
		if ($this->packageName) {
			$this->rootPath = BASE_PATH . '/vendor/' . $this->packageName;
			!$this->packageNamespace && $this->packageNamespace = str_replace('/', '\\', StringHelper::studly($this->packageName));
		} else {
			$reflect = new \ReflectionClass($this);
			$this->packageNamespace = $reflect->getNamespaceName();
			$this->rootPath = dirname($reflect->getFileName(), 2);
		}

		$this->config = iconfig();
		$this->router = irouter();
		$this->logger = ilogger();
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
			$this->rootPath . '/config/' . $sourceFileName => BASE_PATH . '/config/' . $targetFileName
		], $group);
	}

	protected function registerRoute($fileName) {
		$this->router->group([
			'namespace' => $this->packageNamespace,
			'module' => $this->name
		], function () use ($fileName) {
			$this->loadRouteFrom($this->rootPath . '/route/' . $fileName);
		});
	}

	protected function registerStaticResource() {
		$config = $this->config->getServer();
		if (empty($config['common']['document_root'])) {
			throw new \RuntimeException("please set server['common']['document_root']");
		}

		$filesystem = new Filesystem();
		$config = $this->config->getServer();
		$config['common']['document_root'] = rtrim($config['common']['document_root'], '/');
		$flagFilePath = $config['common']['document_root'] . '/' . $this->name . '/resource.lock';

		if ($filesystem->exists($this->rootPath . '/resource') && !$filesystem->exists($flagFilePath)) {
			$filesystem->copyDirectory($this->rootPath . '/resource', $config['common']['document_root'] . '/' . $this->name);
			$filesystem->put($flagFilePath, '');
		}
	}

	public function getView() {
		if (!class_exists(View::class)) {
			throw new \RuntimeException('class ' . View::class . ' not exists');
		}
		/**
		 * @var View $view
		 */
		$view = icontainer()->singleton(View::class);
		return $view;
	}

	protected function registerView($namespace) {
		$this->getView()->addProviderTemplatePath($namespace, $this->rootPath . '/view/');
	}

	protected function registerProvider($provider) {
		icontainer()->singleton(ProviderManager::class)->registerProvider($provider);
	}

	protected function registerCommand($namespace = '') {
		if (!isCli()) {
			return false;
		}
		/**
		 * @var  Application $application
		 */
		$application = icontainer()->singleton(Application::class);
		$application->autoRegisterCommands($this->rootPath . '/src/Command', $this->packageNamespace, $namespace);
	}

	protected function registerOpenBaseDir($dir) {
		$dir = (array)$dir;
		$config = iconfig()->getUserConfig('app');
		$config['setting']['basedir'] = (array)($config['setting']['basedir'] ?? []);
		$config['setting']['basedir'] = array_merge($config['setting']['basedir'], $dir);
		iconfig()->setUserConfig('app', $config);
	}

	protected function registerServer($name, $class) {
		ServerEnum::registerServer($name, $class);
	}

	protected function registerEvent($event, $listener) {
		ieventDispatcher()->listen($event, $listener);
	}

	/**
	 * @param $name
	 * @param array $events
	 * @param bool $cover 是否覆盖已注册的事件
	 */
	protected function registerServerEvent($name, array $events, $cover = false) {
		/**
		 * @var ServerEvent $event
		 */
		$event = icontainer()->singleton(ServerEvent::class);
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
		$config = $this->config->getUserConfig($key);
		$this->config->setUserConfig($key, array_merge(require $path, $config));
	}

	/**
	 * Load the given routes file
	 * 不支持配置文件的路由配置方式
	 * @param $path
	 */
	protected function loadRouteFrom($path) {
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
}
