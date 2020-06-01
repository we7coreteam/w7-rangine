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
use W7\Core\Container\Event\AttributeNotExistsEvent;
use W7\Core\Database\Provider\DatabaseProvider;
use W7\Core\Exception\Provider\ExceptionProvider;
use W7\Core\Provider\Listener\AttributeNotExistsListener;

class ProviderManager {
	private $providerMap = [
		ExceptionProvider::class,
		CacheProvider::class,
		DatabaseProvider::class,
		ValidateProvider::class
	];
	private $deferredProviders = [];
	private $registeredProviders = [];

	public function getDependDeferredProvider($name) {
		return $this->deferredProviders[$name] ?? '';
	}

	public function hasRegister($provider) {
		if (is_object($provider)) {
			$provider = get_class($provider);
		}

		return empty($this->registeredProviders[$provider]) ? false : true;
	}

	/**
	 * 扩展包注册
	 */
	public function register() {
		$providers = iconfig()->get('provider.providers', []);
		$this->registerProviders(array_merge($this->providerMap, $providers));

		if ($this->deferredProviders = iconfig()->get('provider.deferred', [])) {
			ieventDispatcher()->listen(AttributeNotExistsEvent::class, AttributeNotExistsListener::class);
		}

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

	public function registerProviders(array $providerMap) {
		foreach ($providerMap as $name => $providers) {
			$providers = (array) $providers;
			foreach ($providers as $provider) {
				$this->registerProvider($provider, $name);
			}
		}
	}

	public function registerProvider($provider, $name = null) {
		if ($this->hasRegister($provider)) {
			return false;
		}

		if (is_string($provider)) {
			if ((ENV & DEBUG) === DEBUG && !class_exists($provider)) {
				return false;
			}
			$provider = new $provider($name);
		}

		/**
		 * @var ProviderAbstract $provider
		 */
		$provider->register();
		$this->registeredProviders[get_class($provider)] = $provider;

		return $provider;
	}

	public function bootProvider(ProviderAbstract $provider) {
		$provider->boot();
	}
}
