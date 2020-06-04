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

use W7\Core\Cache\Cache;
use W7\Core\Provider\ProviderAbstract;

class CacheProvider extends ProviderAbstract {
	public function register() {
		$config = $this->config->get('app.cache', []);
		$channels = array_keys($config);
		foreach ($channels as $key => $channel) {
			$this->container->set('cache-' . $channel, function () use ($channel) {
				if ($channel === 'default') {
					return icache();
				}
				$cache = new Cache();
				$cache->setChannelName($channel);
				return $cache;
			});
		}
	}
}
