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

namespace W7\Mqtt\Server;

use Psr\Http\Message\ServerRequestInterface;
use W7\Core\Dispatcher\RequestDispatcher;

class Dispatcher extends RequestDispatcher {
	protected function getRoute(ServerRequestInterface $request) {
		$route = parent::getRoute($request);
		$route->args = array_values($route->args);

		return $route;
	}
}
