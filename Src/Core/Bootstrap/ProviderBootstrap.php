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

namespace W7\Core\Bootstrap;

use W7\App;
use W7\Core\Provider\ProviderManager;

class ProviderBootstrap implements BootstrapInterface {
	public function bootstrap(App $app) {
		$app->getContainer()->singleton(ProviderManager::class)->register()->boot();
	}
}
