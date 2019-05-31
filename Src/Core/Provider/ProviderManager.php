<?php

namespace W7\Core\Provider;

class ProviderManager {
	private static $providers = [];

	/**
	 * 扩展包注册
	 */
	public function register() {
		$providers = iconfig()->getUserConfig('provider');
		foreach ($providers as $provider) {
			$this->registerProvider($provider);
		}
		return $this;
	}

	public function registerProvider($provider) {
		if (is_string($provider)) {
			$provider = $this->getProvider($provider);
		}
		static::$providers[get_class($provider)] = $provider;
		$provider->register();
	}

	/**
	 * 扩展包全部注册完成后执行
	 */
	public function boot() {
		foreach (static::$providers as $provider => $obj) {
			$obj->boot();
		}
	}

	private function getProvider($provider) : ProviderAbstract {
		return new $provider();
	}
}
