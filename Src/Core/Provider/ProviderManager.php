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
use W7\Core\Controller\ValidateProvider;
use W7\Core\Database\Provider\DatabaseProvider;
use W7\Core\Exception\Provider\ExceptionProvider;

class ProviderManager {
	private $providerMap = [
		ExceptionProvider::class,
		CacheProvider::class,
		DatabaseProvider::class,
		ValidateProvider::class
	];
	private static $registerProviders = [];

	/**
	 * 扩展包注册
	 */
	public function register() {
		$providers = iconfig()->getUserConfig('provider');
		$this->registerProviders(array_merge($this->providerMap, $providers));
		return $this;
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
		if (is_string($provider)) {
			$provider = $this->getProvider($provider, $name);
		}
		static::$registerProviders[get_class($provider)] = $provider;
		$provider->register();
	}

	/**
	 * 扩展包全部注册完成后执行
	 */
	public function boot() {
		foreach (static::$registerProviders as $provider => $obj) {
			$obj->boot();
		}
	}

	private function getProvider($provider, $name) : ProviderAbstract {
		return new $provider($name);
	}
}
