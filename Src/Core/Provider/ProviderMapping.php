<?php

namespace W7\Core\Provider;

class ProviderMapping {
	private $providers;

	/**
	 * 扩展包发布调用
	 */
	public function publish() {
		$this->initProviders();
		foreach ($this->providers as $provider => $obj) {
			$obj->publish();
		}
	}

	/**
	 * 扩展包注册到框架调用，isDeferred延时注册（预留）
	 * @param bool $isDeferred
	 */
	public function register($isDeferred = false) {
		$this->initProviders();
		foreach ($this->providers as $provider => $obj) {
			if ($obj->isDeferred() == $isDeferred) {
				$obj->register();
			}
		}
	}

	private function initProviders () {
		$providers = iconfig()->getUserAppConfig('providers');
		foreach ($providers as $provider) {
			$this->providers[$provider] = $this->getProvider($provider);
		}
	}

	private function getProvider($provider) {
		return iloader()->singleton($provider);
	}
}