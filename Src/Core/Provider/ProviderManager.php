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

	public function setDeferredProviders(array $deferredProviders) {
		$this->deferredProviders = $deferredProviders;
	}

	/**
	 * @param array $providerMap
	 * @return $this
	 */
	public function register(array $providerMap) {
		foreach ($providerMap as $name => $providers) {
			$providers = (array) $providers;
			foreach ($providers as $provider) {
				$this->registerProvider($provider, $name);
			}
		}
		return $this;
	}

	/**
	 * Execute the extension package after all registration is completed
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

	public function registerProvider($provider, $name = null, $force = false) {
		if ($this->hasRegister($provider)) {
			return false;
		}

		if (!$force) {
			//Checks if the service is already loaded lazily
			foreach ($this->deferredProviders as $providers) {
				if (in_array($provider, $providers)) {
					return false;
				}
			}
		}

		if (is_string($provider)) {
			if ((ENV & DEBUG) === DEBUG && !class_exists($provider)) {
				return false;
			}
			$providerClass = $provider;
			$provider = new $providerClass($name);
			$this->container->set($providerClass, $provider);
		}

		/**
		 * @var ProviderAbstract $provider
		 */
		$this->registeredProviders[get_class($provider)] = $provider;
		$provider->register();

		return $provider;
	}

	public function bootProvider(ProviderAbstract $provider) {
		$provider->boot();
	}
}
