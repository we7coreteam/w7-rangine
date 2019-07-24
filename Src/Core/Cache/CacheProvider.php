<?php

namespace W7\Core\Cache;

use W7\Core\Provider\ProviderAbstract;
use W7\Laravel\CacheModel\Caches\Cache;

class CacheProvider extends ProviderAbstract {
	public function boot() {
		// TODO: Implement register() method.
		$this->registerCacheModel();
	}

	protected function registerCacheModel() {
		$config = iconfig()->getUserAppConfig('cache');
		if (!empty($config['default']) && !empty($config['default']['model']) && !empty($config['default']['host']) && !empty($config['default']['port'])) {
			Cache::setCacheResolver(icache());
		}
	}
}