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

namespace W7\Core\Cache\Provider;

use W7\Contract\Cache\CacheFactoryInterface;
use W7\Core\Cache\CacheFactory;
use W7\Core\Provider\ProviderAbstract;

class CacheProvider extends ProviderAbstract {
	public function register() {
		$this->container->set(CacheFactoryInterface::class, function () {
			$cacheConfig = $this->config->get('app.cache', []);
			foreach ($cacheConfig as &$config) {
				$config['driver'] = $this->config->get('handler.cache.' . $config['driver'], $config['driver']);
			}

			return new CacheFactory($cacheConfig);
		});
	}

	public function providers(): array {
		return [CacheFactoryInterface::class];
	}
}
