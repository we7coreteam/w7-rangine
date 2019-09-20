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

namespace W7\Core\Database\Provider;

use W7\Core\Provider\ProviderAbstract;
use W7\Laravel\CacheModel\Caches\Cache;

class CacheModelProvider extends ProviderAbstract {
	public function register() {
		$config = iconfig()->getUserAppConfig('cache');
		if (!empty($config['default']) && !empty($config['default']['model']) && !empty($config['default']['host']) && !empty($config['default']['port'])) {
			Cache::setCacheResolver(icache());
		}
	}
}
