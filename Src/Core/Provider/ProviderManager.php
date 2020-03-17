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
use W7\Core\Cache\Provider\CacheProvider;
use W7\Core\Controller\ValidateProvider;
use W7\Core\Database\Provider\DatabaseProvider;
use W7\Core\Exception\Provider\ExceptionProvider;
use W7\Reload\Process\ReloadProcess;

class ProviderManager {
	private static $providerMap = [
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
		$providerMap = $this->findProviders();
		$this->registerProviders(array_merge(self::$providerMap, $providerMap));
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

	private function findProviders() {
		//自动注册系统provider,不包括服务
		$vendorProviders = $this->findVendorProviders();
		$appProvider = $this->autoFindProviders(BASE_PATH . '/app/Provider', 'W7/App/Provider');

		return array_merge($vendorProviders, $appProvider);
	}

	public function autoFindProviders($dir, $namespace) {
		if (!is_dir($dir)) {
			return [];
		}
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
		if (PHP_SAPI == 'cli' && (ENV & DEBUG) !== DEBUG) {
			return '';
		}

		if ($conf[$conf['installation-source']]['type'] == 'path') {
			$path = BASE_PATH . '/' . $conf[$conf['installation-source']]['url'];

			$config = iconfig()->getUserConfig('app');
			$config['setting']['basedir'] = (array)($config['setting']['basedir'] ?? []);
			$config['setting']['basedir'][] = $path;
			iconfig()->setUserConfig('app', $config);
		} else {
			$path = BASE_PATH . '/vendor/' . $conf['name'];
		}
		$path .= '/';

		ReloadProcess::addDir($path);
	}
}
