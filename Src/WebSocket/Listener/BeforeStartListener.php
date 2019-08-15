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

use W7\App;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Route\RouteMapping;
use W7\Core\Helper\Storage\Context;
use FastRoute\Dispatcher\GroupCountBased;

class BeforeStartListener extends ListenerAbstract {
	public function run(...$params) {
		$context = App::getApp()->getContext();
		//注册路由的时候会调用中间件生成，所以要先生成路由再中间件
		$context->setContextDataByKey(Context::ROUTE_KEY, $this->getRoute());
		return true;
	}

	/**
	 * @return GroupCountBased
	 */
	private function getRoute() {
		$routeInfo = iloader()->singleton(RouteMapping::class)->getMapping();
		return new GroupCountBased($routeInfo);
	}
}
