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
use W7\Core\Route\Route;

class Dispatcher extends RequestDispatcher {
	/**
	 * @var Route
	 */
	protected $route;
	protected $uriPattern;

	public function setRoute(Route $route) {
		$this->route = $route;

		$uri = $this->route->getUri();

		$uri = str_replace('/', '\/', $uri);
		$uriArr = explode('#', $uri);
		$uri = array_shift($uriArr);
		foreach ($uriArr as $item) {
			$uri .= "([\s\S]*)" . $item;
		}
		$uriArr = explode('+', $uri);
		$uri = array_shift($uriArr);
		foreach ($uriArr as $item) {
			$uri .= "([^\/]+)" . $item;
		}
		$uri = "/^" . $uri . "/";

		$this->uriPattern = $uri;
	}

	protected function getRoute(ServerRequestInterface $request) {
		$path = $request->getUri()->getPath();

		$matches = [];
		preg_match($this->uriPattern, $path, $matches);
		array_shift($matches);

		$route = clone $this->route;
		$route->args = $matches;
		$route->uri = $path;

		return $route;
	}
}
