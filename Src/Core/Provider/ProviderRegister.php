<?php

namespace W7\Core\Provider;

use W7\Core\Service\ServiceAbstract;

class ProviderRegister extends ServiceAbstract {
	private static $providers = [];

	/**
	 * 扩展包注册
	 */
	public function register() {
		$providerMap = $this->findProviders();
		foreach ($providerMap as $name => $providers) {
			$providers = (array) $providers;
			foreach ($providers as $provider) {
				$this->registerProvider($provider);
			}
		}
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

	private function findProviders() {
		ob_start();
		require_once BASE_PATH . '/vendor/composer/installed.json';
		$content = ob_get_clean();
		$content = json_decode($content, true);

		$providers = [];
		foreach ($content as $item) {
			if (!empty($item['extra']['rangine']['providers'])) {
				$providers[str_replace('/', '.', $item['name'])] = $item['extra']['rangine']['providers'];
			}
		}

		return $providers;
	}
}
