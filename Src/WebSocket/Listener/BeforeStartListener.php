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

namespace W7\WebSocket\Listener;

use W7\Core\Facades\Container;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Route\RouteDispatcher;
use W7\Core\Server\ServerEnum;
use W7\WebSocket\Route\RouteMapping;
use W7\WebSocket\Server\Dispatcher;
use W7\WebSocket\Session\Middleware\SessionMiddleware;

class BeforeStartListener extends ListenerAbstract {
	public function run(...$params) {
		$this->registerRouter();
		$this->registerMiddleware();
	}

	private function registerRouter() {
		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = Container::singleton(Dispatcher::class);
		$dispatcher->setRouterDispatcher(RouteDispatcher::getDispatcherWithRouteMapping(RouteMapping::class, ServerEnum::TYPE_WEBSOCKET));
	}

	private function registerMiddleware() {
		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = Container::singleton(Dispatcher::class);
		$dispatcher->getMiddlewareMapping()->addBeforeMiddleware(SessionMiddleware::class, true);
	}
}
