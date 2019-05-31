<?php

namespace W7\Core\Provider;

use W7\Core\Route\RouteMapping;

abstract class ProviderAbstract {
	protected $config;
	protected $router;
	protected $defer;
	public static $publishes = [];
	public static $publishGroups = [];
	protected $rootPath;

	public function __construct() {
		$this->config = iconfig();
		$this->router = irouter();
		$this->rootPath = dirname((new \ReflectionClass($this))->getFileName(), 2);
	}

	/**
	 * Register any application services.
	 * @return void
	 */
	public function register() {}

	/**
	 * boot any application services
	 * @return mixed
	 */
	public function boot() {}

	protected function registerConfig ($path, $key) {
		$this->mergeConfigFrom($this->rootPath . '/config/' . $path, $key);
	}

	protected function registerRoute($route) {
		$this->loadRouteFrom($this->rootPath . '/route/' . $route);
	}

	protected function registerProvider($provider) {
		iloader()->singleton(ProviderManager::class)->registerProvider($provider);
	}

	protected function registerCommand($name, $class) {
		$userCommands = $this->config->getUserConfig('command');
		$this->config->setUserConfig('command', array_merge($userCommands, [$name => $class]));
	}

	protected function publishConfig($source, $target = null, $group = null) {
		if (!$target) {
			$target = $source;
		}
		$this->publishes([
			$this->rootPath . '/config/' . $source => BASE_PATH . '/config/' . $target
		], $group);
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
			static::$publishGroups[$group], $paths
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

	/**
	 * Determine if the provider is deferred.
	 *
	 * @return bool
	 */
	public function isDeferred() {
		return $this->defer;
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {
		throw new \Exception('还未实现');
	}
}
