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

use W7\Core\Container\Container;

class ProviderManager {
	/**
	 * @var Container
	 */
	protected $container;
	protected $deferredProviders = [];
	protected $registeredProviders = [];

	public function __construct(Container $container) {
		$this->container = $container;

		$container->registerDeferredServiceLoader(function ($service) {
			$providers = $this->deferredProviders[$service] ?? [];
			foreach ($providers as $provider) {
				$provider = $this->registerProvider($provider, $provider, true);
				$provider && $this->bootProvider($provider);
			}
		});
	}

	/**
	 * @param array $providers
	 * @return $this
	 */
	public function register(array $providers) {
		$this->registerProviders($providers);
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
			$provider = $this->container->singleton($provider, $params);
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
				$this->container->registerDeferredService($deferredServices);
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
