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

namespace W7\Core\Session\Provider;

use W7\Core\Middleware\MiddlewareMapping;
use W7\Core\Provider\ProviderAbstract;
use W7\Core\Session\Middleware\SessionMiddleware;

class SessionProvider extends ProviderAbstract {
	public function register() {
		//不管是否需要都会开启session 需要优化
		iloader()->get(MiddlewareMapping::class)->addBeforeMiddleware(SessionMiddleware::class);
	}
}
