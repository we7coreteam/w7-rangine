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

use W7\Core\Database\CacheModelProvider;
use W7\Core\Database\DatabaseProvider;
use W7\Core\Lang\TranslatorProvider;

class ProviderManager {
	private $providerMap = [
		DatabaseProvider::class,
		CacheModelProvider::class,
		TranslatorProvider::class
	];
	private static $providers = [];

	/**
	 * 扩展包注册
	 */
	public function register() {
		$providerMap = $this->findProviders();
		foreach ($this->providerMap as $key => $provider) {
			$providerMap[$provider] = $provider;
		}
		foreach ($providerMap as $name => $providers) {
			$providers = (array) $providers;
			foreach ($providers as $provider) {
				$this->registerProvider($provider, $name);
			}
		}
		return $this;
	}

	public function registerProvider($provider, $name = null) {
		if (is_string($provider)) {
			$provider = $this->getProvider($provider, $name);
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

	private function getProvider($provider, $name) : ProviderAbstract {
		return new $provider($name);
	}

	private function findProviders() {
		ob_start();
		require_once BASE_PATH . '/vendor/composer/installed.json';
		$content = ob_get_clean();
		$content = json_decode($content, true);

		$providers = [];
		$reloadPath = [];
		foreach ($content as $item) {
			if (!empty($item['extra']['rangine']['providers'])) {
				$providers[str_replace('/', '.', $item['name'])] = $item['extra']['rangine']['providers'];
				$reloadPath[] = $this->getProviderPath($item);
			}
		}
		$this->setReloadListenerPath($reloadPath);

		return $providers;
	}

	private function getProviderPath($conf) {
		if ((ENV & DEBUG) !== DEBUG) {
			return '';
		}

		if ($conf[$conf['installation-source']]['type'] == 'path') {
			$path = BASE_PATH . '/' . $conf[$conf['installation-source']]['url'];
		} else {
			$path = BASE_PATH . '/vendor/' . $conf['name'];
		}

		$path .= '/';
		return $path;
	}

	private function setReloadListenerPath($reloadPath) {
		if ((ENV & DEBUG) !== DEBUG) {
			return false;
		}

		$config = iconfig()->getUserConfig('app');
		$config['reload']['path'] = $reloadPath;
		iconfig()->setUserConfig('app', $config);
	}
}
