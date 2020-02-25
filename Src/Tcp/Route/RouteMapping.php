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

namespace W7\Tcp\Route;

use W7\Core\Route\Route;
use W7\Core\Route\RouteMapping as RouteMappingAbstract;

class RouteMapping extends RouteMappingAbstract {
	/**
	 * @return array|mixed
	 */
	public function getMapping() {
		if (!empty($this->routeConfig)) {
			foreach ($this->routeConfig as $index => $routeConfig) {
				$this->initRouteByConfig($routeConfig);
			}
		}
		$this->registerSystemRoute();

		$routes = $this->router->getData();
		$this->router = new Route();
		$this->processRoutes($routes);

		return $this->router->getData();
	}

	protected function processRoutes($routeMap) {
		$registerRoutes = [];
		foreach ($routeMap[0] as $method => $routes) {
			foreach ($routes as $key => $route) {
				if (!in_array($route['uri'], $registerRoutes)) {
					$this->router->getRouter()->addRoute('POST', $route['uri'], $route);
				}
				$registerRoutes[] = $route['uri'];
			}
		}

		foreach ($routeMap[1] as $method => $routeGroup) {
			foreach ($routeGroup as $routes) {
				foreach ($routes['routeMap'] as $route) {
					$route = $route[0];
					if (!in_array($route['uri'], $registerRoutes)) {
						$this->router->getRouter()->addRoute('POST', $route['uri'], $route);
					}
				}
			}
		}
	}
}
