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

use W7\Core\Cache\Provider\CacheProvider;
use W7\Core\Database\Provider\DatabaseProvider;
use W7\Core\Events\Provider\EventProvider;
use W7\Core\Facades\Config;
use W7\Core\Facades\Container;
use W7\Core\Log\Provider\LogProvider;
use W7\Core\Route\Provider\RouterProvider;
use W7\Core\Session\Provider\SessionProvider;
use W7\Core\View\Provider\ViewProvider;

class ProviderManager {
	private $providerMap = [
		'illuminate' => IlluminateProvider::class,
		'event' => EventProvider::class,
		'log' => LogProvider::class,
		'router' => RouterProvider::class,
		'database' => DatabaseProvider::class,
		'cache' => CacheProvider::class,
		'view' => ViewProvider::class,
		'validate' => ValidateProvider::class,
		'session' => SessionProvider::class
	];
	private $deferredProviders = [];
	private $registeredProviders = [];

	public function __construct() {
		Container::registerDeferredServiceLoader(function ($service) {
			$providers = $this->deferredProviders[$service] ?? [];
			foreach ($providers as $provider) {
				$provider = $this->registerProvider($provider, $provider, true);
				$provider && $this->bootProvider($provider);
			}
		});
	}

	/**
	 * 扩展包注册
	 */
	public function register() {
		$providers = Config::get('provider', []);
		$this->registerProviders(array_merge($this->providerMap, $providers));
		return $this;
	}

	/**
	 * 扩展包全部注册完成后执行
	 */
	public function boot() {
		foreach ($this->registeredProviders as $name => $provider) {
			$this->bootProvider($provider);
		}
	}

	public function hasRegister($provider) {
		if (is_object($provider)) {
			$provider = get_class($provider);
		}

		return empty($this->registeredProviders[$provider]) ? false : true;
	}

	public function registerProviders(array $providerMap) {
		foreach ($providerMap as $name => $providers) {
			$providers = (array) $providers;
			foreach ($providers as $provider) {
				$this->registerProvider($provider, $name);
			}
		}
	}

	public function registerProvider($provider, $name = null, $force = false) {
		if ($this->hasRegister($provider)) {
			return false;
		}

		if (is_string($provider)) {
			if ((ENV & DEBUG) === DEBUG && !class_exists($provider)) {
				return false;
			}
			$params = isset($name) ? [$name] : [];
			$provider = Container::singleton($provider, $params);
		}

		/**
		 * @var ProviderAbstract $provider
		 */
		//如果是强制注册，不对是否有依赖服务进行检测,直接注册
		if (!$force) {
			$deferredServices = $provider->providers();
			//如果有延迟加载服务，不对其进行注册
			if ($deferredServices) {
				foreach ($deferredServices as $deferredService) {
					$this->deferredProviders[$deferredService] = $this->deferredProviders[$deferredService] ?? [];
					$this->deferredProviders[$deferredService] = array_merge($this->deferredProviders[$deferredService], [get_class($provider)]);
				}
				Container::registerDeferredService($deferredServices);
				return false;
			}
		}

		$provider->register();
		$this->registeredProviders[get_class($provider)] = $provider;

		return $provider;
	}

	public function bootProvider(ProviderAbstract $provider) {
		$provider->boot();
	}
}
