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

namespace W7\WebSocket\Session\Provider;

use W7\Core\Provider\ProviderAbstract;
use W7\WebSocket\Server\Dispatcher;
use W7\WebSocket\Session\Middleware\SessionMiddleware;

class SessionProvider extends ProviderAbstract {
	public function register() {
		$this->registerMiddleware();
	}

	private function registerMiddleware() {
		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = $this->container->singleton(Dispatcher::class);
		$dispatcher->getMiddlewareMapping()->addBeforeMiddleware(SessionMiddleware::class);
	}
}
