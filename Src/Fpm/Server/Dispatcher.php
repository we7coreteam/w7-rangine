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

namespace W7\Fpm\Server;

use Psr\Http\Message\ServerRequestInterface;
use W7\Core\Dispatcher\RequestDispatcher;
use W7\Core\Route\Route;

class Dispatcher extends RequestDispatcher {
	protected function getRoute(ServerRequestInterface $request): Route {
		$httpMethod = $request->getMethod();
		$path = $request->getUri()->getPath();
		if ($path === '/' && !empty($request->getQueryParams()['r'])) {
			$uri = $request->getQueryParams()['r'];
		} else {
			$uri = $path;
		}

		return $this->getRouteByMethodAndUrl($httpMethod, $uri);
	}
}
