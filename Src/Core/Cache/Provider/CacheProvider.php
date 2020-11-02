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

use W7\Core\Cache\CacheFactory;
use W7\Core\Cache\ConnectionResolver;
use W7\Core\Cache\Pool\PoolFactory;
use W7\Core\Facades\Event;
use W7\Core\Provider\ProviderAbstract;

class CacheProvider extends ProviderAbstract {
	public function register() {
		$this->container->set(ConnectionResolver::class, function () {
			$connectionConfig = $this->config->get('app.cache', []);
			foreach ($connectionConfig as &$config) {
				$config['driver'] = $this->config->get('handler.cache.' . $config['driver'], $config['driver']);
			}
			$poolConfig = $this->config->get('app.pool.cache', []);

			$connectionResolver = new ConnectionResolver($connectionConfig);
			$connectionResolver->setEventDispatcher(Event::getFacadeRoot());
			$connectionResolver->setPoolFactory(new PoolFactory($poolConfig));

			return $connectionResolver;
		});
		$this->container->set(CacheFactory::class, function () {
			$cacheFactoryr = new CacheFactory();
			$cacheFactoryr->setConnectionResolver($this->container->get(ConnectionResolver::class));
			return $cacheFactoryr;
		});
	}

	public function providers(): array {
		return [CacheFactory::class];
	}
}
