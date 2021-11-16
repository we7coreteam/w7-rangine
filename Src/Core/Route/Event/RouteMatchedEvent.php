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

namespace W7\Core\Route\Event;

use W7\Core\Route\Route;
use W7\Http\Message\Server\Request;

class RouteMatchedEvent {
	public Route $route;
	public Request $request;

	public function __construct($route, $request) {
		$this->route = $route;
		$this->request = $request;
	}
}
