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

use W7\Core\Helper\Storage\Context;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Route\RouteMapping;
use FastRoute\Dispatcher\GroupCountBased;
use W7\WebSocket\Parser\JsonParser;
use W7\WebSocket\Parser\ParserInterface;

class BeforeStartListener extends ListenerAbstract {
	public function run(...$params) {
		iloader()->set(Context::ROUTE_KEY, $this->getRoute());
		$this->setMessageParse();
	}

	/**
	 * @return GroupCountBased
	 */
	private function getRoute() {
		$routeInfo = iloader()->get(RouteMapping::class)->getMapping();
		return new GroupCountBased($routeInfo);
	}

	private function setMessageParse() {
		iloader()->set(ParserInterface::class, function () {
			return new JsonParser();
		});
	}
}
