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
use W7\Core\Route\RouteMapping;
use W7\Core\View\View;

abstract class ProviderAbstract {
	protected $name;
	protected $namespace;
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
	/**
	 * @var View
	 */
	protected $view;
	protected $defer;
	public static $publishes = [];
	public static $publishGroups = [];
	protected $rootPath;

	public function __construct($name = null) {
		if (!$name) {
			$name = get_called_class();
		}
		$this->name = $name;
		$reflect = new \ReflectionClass($this);
		$this->namespace = $reflect->getNamespaceName();
		$this->rootPath = dirname($reflect->getFileName(), 2);

		$this->config = iconfig();
		$this->router = irouter();
		$this->logger = ilogger();
		$this->view = iloader()->singleton(View::class);
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
		if (!$targetFileName) {
			$targetFileName = $sourceFileName;
		}
		$this->publishes([
			$this->rootPath . '/config/' . $sourceFileName => BASE_PATH . '/config/' . $targetFileName
		], $group);
	}

	protected function registerRoute($fileName) {
		$this->router->group([
			'namespace' => $this->namespace,
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

	protected function registerView($namespace) {
		$this->view->addProviderTemplatePath($namespace, $this->rootPath . '/view/');
	}

	protected function registerProvider($provider) {
		iloader()->singleton(ProviderManager::class)->registerProvider($provider);
	}

	protected function registerCommand($group = null, $forceGroup = false) {
		/**
		 * @var  Application $application
		 */
		$application = iloader()->singleton(Application::class);
		$application->autoRegisterCommands($this->rootPath . '/src/Command', $this->namespace, $group ?? $this->name, $forceGroup);
	}

	protected function registerProcess($name, $class) {
		$appCofig = $this->config->getUserConfig('app');
		$appCofig['process'][$name] = [
			'enable' => ienv('PROCESS_' . strtoupper($name) . '_ENABLE', false),
			'class' => $class,
			'number' => ienv('PROCESS_' . strtoupper($name) . '_NUMBER', 1)
		];

		$this->config->setUserConfig('app', $appCofig);
	}

	protected function setRootPath($path) {
		$this->rootPath = $path;
	}

	/**
	 * Merge the given configuration with the existing configuration.
	 * @param $path
	 * @param $key
	 * @return bool
	 */
	protected function mergeConfigFrom($path, $key) {
		$config = $this->config->getUserConfig($key);
		$this->config->setUserConfig($key, array_merge(require $path, $config));
	}

	/**
	 * Load the given routes file
	 * @param $path
	 */
	protected function loadRouteFrom($path) {
		$config = include $path;
		if (is_array($config)) {
			/**
			 * @var RouteMapping $routeMapping
			 */
			$routeMapping = iloader()->singleton(RouteMapping::class);
			$routeConfig = $routeMapping->getRouteConfig();
			$routeConfig[] = $config;
			$routeMapping->setRouteConfig($routeConfig);
		}
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
