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

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use W7\Reload\Process\ReloadProcess;

class ProviderManager {
	private static $providers = [];

	/**
	 * 扩展包注册
	 */
	public function register() {
		$providerMap = $this->findProviders();
		$this->checkRepeat($providerMap);
		$this->registerProviders($providerMap);
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
		$systemProviders = $this->autoFindProviders(dirname(__DIR__, 2), 'W7');
		$vendorProviders = $this->findVendorProviders();
		$appProvider = $this->autoFindProviders(BASE_PATH . '/app', 'W7/App');

		return array_merge($systemProviders, $vendorProviders, $appProvider);
	}

	public function autoFindProviders($dir, $namespace) {
		$providers = [];

		$files = Finder::create()
			->in($dir)
			->files()
			->ignoreDotFiles(true)
			->name('/^[\w\W\d]+Provider.php$/');

		/**
		 * @var SplFileInfo $file
		 */
		foreach ($files as $file) {
			$path = str_replace([$dir, '.php', '/'], [$namespace, '', '\\'], $file->getRealPath());
			$providers[$path] = $path;
		}

		return $providers;
	}

	private function findVendorProviders() {
		ob_start();
		require BASE_PATH . '/vendor/composer/installed.json';
		$content = ob_get_clean();
		$content = json_decode($content, true);

		$providers = [];
		foreach ($content as $item) {
			if (!empty($item['extra']['rangine']['providers'])) {
				$providers[str_replace('/', '.', $item['name'])] = $item['extra']['rangine']['providers'];
				$this->addReloadPath($item);
			}
		}

		return $providers;
	}

	private function addReloadPath($conf) {
		if ((ENV & DEBUG) !== DEBUG) {
			return '';
		}

		if ($conf[$conf['installation-source']]['type'] == 'path') {
			$path = BASE_PATH . '/' . $conf[$conf['installation-source']]['url'];
		} else {
			$path = BASE_PATH . '/vendor/' . $conf['name'];
		}
		$path .= '/';

		ReloadProcess::addDir($path);
	}

	private function checkRepeat($providerMap) {
		$map = [];
		foreach ($providerMap as $key => $providers) {
			$providers = (array)$providers;
			foreach ($providers as $provider) {
				if (!empty($map[$provider])) {
					throw new \RuntimeException('provider ' . $key . ' and ' . $map[$provider] . ' provider is repeat');
				}
				$map[$provider] = $key;
			}
		}
	}
}
