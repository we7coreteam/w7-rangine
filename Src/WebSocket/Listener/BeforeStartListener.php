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

use W7\Core\Listener\ListenerAbstract;
use FastRoute\Dispatcher\GroupCountBased;
use W7\WebSocket\Route\RouteMapping;
use W7\WebSocket\Server\Dispatcher;

class BeforeStartListener extends ListenerAbstract {
	public function run(...$params) {
		$this->registerRouter();
	}

	private function registerRouter() {
		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = icontainer()->get(Dispatcher::class);
		$routeInfo = icontainer()->get(RouteMapping::class)->getMapping();
		$dispatcher->setRouter(new GroupCountBased($routeInfo));
	}
}
