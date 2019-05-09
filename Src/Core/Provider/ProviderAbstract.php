<?php

namespace W7\Core\Provider;

use W7\Core\Route\RouteMapping;

abstract class ProviderAbstract {
	protected $config;
	protected $router;
	protected $defer;
	public static $publishes = [];
	public static $publishGroups = [];


	public function __construct() {
		$this->config = iconfig();
		$this->router = irouter();
	}

	/**
	 * Register any application services.
	 * @return void
	 */
	abstract public function register();

	/**
	 * boot any application services
	 * @return mixed
	 */
	abstract public function boot();

	/**
	 * register vendor provider
	 * @param $provider
	 */
	protected function registerProvider($provider) {
		iloader()->singleton(ProviderManager::class)->registerProvider($provider);
	}
	/**
	 * Merge the given configuration with the existing configuration.
	 *
	 * @param  string  $path
	 * @param  string  $key
	 * @return void
	 */
	protected function mergeConfigFrom($path, $key) {
		$config = $this->config->getUserConfig($key);
		$this->config->setUserConfig($key, array_merge(require $path, $config));
	}

	/**
	 * Load the given routes file if routes are not already cached.
	 *
	 * @param  string  $path
	 * @return void
	 */
	protected function loadRoutesFrom($path) {
		$routeConfig = include $path;
		if (is_array($routeConfig)) {
			iloader()->singleton(RouteMapping::class)->initRouteByConfig($routeConfig);
		}
	}

	/**
	 * Register a view file namespace.
	 *
	 * @param  string|array  $path
	 * @param  string  $namespace
	 * @return void
	 */
	protected function loadViewsFrom($path, $namespace) {
		throw new \Exception('还未实现');
	}

	/**
	 * Register the package's custom Artisan commands.
	 *
	 * @param  array|mixed  $commands
	 * @return void
	 */
	protected function addCommands(array $commands) {
		$userCommands = $this->config->getUserConfig('command');
		$this->config->setUserConfig('command', array_merge($userCommands, $commands));
	}

	/**
	 * Register paths to be published by the publish command.
	 *
	 * @param  array  $paths
	 * @param  mixed  $groups
	 * @return void
	 */
	protected function publishes(array $paths, $groups = null) {
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
	 * Get the service providers available for publishing.
	 *
	 * @return array
	 */
	public static function publishableProviders() {
		return array_keys(static::$publishes);
	}

	/**
	 * Get the groups available for publishing.
	 *
	 * @return array
	 */
	public static function publishableGroups() {
		return array_keys(static::$publishGroups);
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
