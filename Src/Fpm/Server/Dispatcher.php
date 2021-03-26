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
use W7\Core\Exception\RouteNotAllowException;
use W7\Core\Exception\RouteNotFoundException;
use W7\Core\Route\Route;
use W7\Core\Route\RouteDispatcher;

class Dispatcher extends RequestDispatcher {
	protected function getRoute(ServerRequestInterface $request) {
		$httpMethod = $request->getMethod();
		//该方法最后在http-message中做兼容
		$pathInfo = $request->getUri()->getPath();
		if ($pathInfo == '/' && !empty($request->getQueryParams()['r'])) {
			$url = $request->getQueryParams()['r'];
		} else {
			$url = $pathInfo;
		}

		$routeData = $this->routerDispatcher->dispatch($httpMethod, $url);

		switch ($routeData[0]) {
			case RouteDispatcher::NOT_FOUND:
				throw new RouteNotFoundException('Route not found, ' . $url, 404);
				break;
			case RouteDispatcher::METHOD_NOT_ALLOWED:
				throw new RouteNotAllowException('Route not allowed, ' . $url . ' with method ' . $httpMethod, 405);
				break;
			case RouteDispatcher::FOUND:
				break;
		}

		return new Route(
			$routeData[1]['name'],
			$routeData[1]['module'],
			$routeData[1]['handler'],
			$routeData[2] ?? [],
			$routeData[1]['middleware']['before'] ?? [],
			$routeData[1]['defaults'] ?? []
		);
	}
}
