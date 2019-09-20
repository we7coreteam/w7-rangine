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

namespace W7\Http\Listener;

use W7\Core\Listener\ListenerAbstract;
use W7\Core\Route\RouteMapping;
use FastRoute\Dispatcher\GroupCountBased;
use W7\Http\Server\Dispatcher;

class BeforeStartListener extends ListenerAbstract {
	public function run(...$params) {
		$this->setRouter();
		return true;
	}

	private function setRouter() {
		/**
		 * @var Dispatcher $requestDispatcher
		 */
		$requestDispatcher = iloader()->get(Dispatcher::class);
		$requestDispatcher->setRouter($this->getRoute());
	}

	/**
	 * @return GroupCountBased
	 */
	private function getRoute() {
		$routeInfo = iloader()->get(RouteMapping::class)->getMapping();
		return new GroupCountBased($routeInfo);
	}
}
