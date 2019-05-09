<?php

namespace W7\Core\Provider;

use W7\Core\Route\RouteMapping;

class ProviderManager {
	private $providers = [];

	public function __construct() {
		$this->initProviders();
	}

	/**
	 * 扩展包注册，isDeferred延时注册（预留）
	 * @param bool $isDeferred
	 */
	public function register($isDeferred = false) {
		foreach ($this->providers as $provider => $obj) {
			if ($obj->isDeferred() == $isDeferred) {
				$obj->register();
			}
		}
		return $this;
	}

	/**
	 * 扩展包全部注册完成后执行
	 */
	public function boot() {
		foreach ($this->providers as $provider => $obj) {
			$obj->boot();
		}
	}

	private function initProviders () {
		$providers = iconfig()->getUserAppConfig('providers');
		foreach ($providers as $provider) {
			$this->providers[$provider] = $this->getProvider($provider);
		}
	}

	private function getProvider($provider) : ProviderAbstract {
		return new $provider([iconfig(), iloader()->singleton(RouteMapping::class)]);
	}
}