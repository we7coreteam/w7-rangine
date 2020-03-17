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
		$providerMap = $this->findProviders();
		$this->registerProviders(array_merge($this->providerMap, $providerMap));
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
		return $this->autoFindProviders(BASE_PATH . '/app/Provider', 'W7/App/Provider');
	}

	public function autoFindProviders($dir, $namespace) {
		$providers = [];
		if (!is_dir($dir)) {
			return $providers;
		}

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
}
