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
	protected Container $container;
	protected array $deferredProviders = [];
	protected array $registeredProviders = [];

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

	public function setDeferredProviders(array $deferredProviders): void {
		$this->deferredProviders = $deferredProviders;
	}

	/**
	 * @param array $providerMap
	 * @return $this
	 */
	public function register(array $providerMap): static {
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
	public function boot(): void {
		foreach ($this->registeredProviders as $name => $provider) {
			$this->bootProvider($provider);
		}
	}

	public function hasRegister($provider): bool {
		if (is_object($provider)) {
			$provider = get_class($provider);
		}

		return !empty($this->registeredProviders[$provider]);
	}

	public function registerProvider($provider, $name = null, $force = false): ?ProviderAbstract {
		if ($this->hasRegister($provider)) {
			return null;
		}

		if (!$force) {
			//Checks if the service is already loaded lazily
			foreach ($this->deferredProviders as $providers) {
				if (in_array($provider, $providers, true)) {
					return null;
				}
			}
		}

		if (is_string($provider)) {
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

	public function bootProvider(ProviderAbstract $provider): void {
		$provider->boot();
	}
}
