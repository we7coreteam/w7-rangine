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
use W7\Core\Route\RouteDispatcher;

class Dispatcher extends RequestDispatcher {
	protected function getRoute(ServerRequestInterface $request) {
		$httpMethod = $request->getMethod();
		//该方法最后在http-message中做兼容
		$pathInfo = $request->getUri()->getPath();
		if ($pathInfo == DIRECTORY_SEPARATOR && !empty($request->getQueryParams()['r'])) {
			$url = $request->getQueryParams()['r'];
		} else {
			$url = $pathInfo;
		}

		$route = $this->routerDispatcher->dispatch($httpMethod, $url);

		$controller = $method = '';
		switch ($route[0]) {
			case RouteDispatcher::NOT_FOUND:
				throw new RouteNotFoundException('Route not found, ' . $url, 404);
				break;
			case RouteDispatcher::METHOD_NOT_ALLOWED:
				throw new RouteNotAllowException('Route not allowed, ' . $url, 405);
				break;
			case RouteDispatcher::FOUND:
				if ($route[1]['handler'] instanceof \Closure) {
					$controller = $route[1]['handler'];
					$method = '';
				} else {
					list($controller, $method) = $route[1]['handler'];
				}
				break;
		}

		return [
			'name' => $route[1]['name'],
			'module' => $route[1]['module'],
			'method' => $method,
			'controller' => $controller,
			'args' => $route[2],
			'middleware' => $route[1]['middleware']['before'],
			'defaults' => $route[1]['defaults']
		];
	}
}
