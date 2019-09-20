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
use W7\WebSocket\Collector\CollectorManager;
use W7\WebSocket\Server\Dispatcher;
use W7\WebSocket\Session\Middleware\SessionMiddleware;
use W7\WebSocket\Session\SessionCollector;

class SessionProvider extends ProviderAbstract {
	public function register() {
		$this->registerCollector();
		$this->registerMiddleware();
	}

	private function registerCollector() {
		iloader()->get(CollectorManager::class)->addCollect(new SessionCollector());
	}

	private function registerMiddleware() {
		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = iloader()->get(Dispatcher::class);
		$dispatcher->getMiddlewareMapping()->addBeforeMiddleware(SessionMiddleware::class);
	}
}
