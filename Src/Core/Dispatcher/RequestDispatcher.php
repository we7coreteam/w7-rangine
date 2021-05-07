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

namespace W7\Core\Dispatcher;

use Psr\Http\Message\ServerRequestInterface;
use W7\Core\Exception\HandlerExceptions;
use W7\Core\Exception\RouteNotAllowException;
use W7\Core\Exception\RouteNotFoundException;
use W7\Core\Middleware\MiddlewareHandler;
use W7\Core\Middleware\MiddlewareMapping;
use W7\Core\Route\Event\RouteMatchedEvent;
use W7\Core\Route\Route;
use W7\Core\Route\RouteDispatcher;
use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;

class RequestDispatcher extends DispatcherAbstract {
	protected $serverType;
	/**
	 * @var MiddlewareMapping
	 */
	protected $middlewareMapping;
	/**
	 * @var RouteDispatcher
	 */
	protected $routerDispatcher;

	public function __construct() {
		//Middleware needs to be separated when different types of servers are started together
		$this->serverType = lcfirst(explode('\\', static::class)[1]);
		$this->middlewareMapping = new MiddlewareMapping();

		foreach ($this->getConfig()->get('middleware.' . strtolower($this->serverType) . '.before', []) as $middleware) {
			$this->middlewareMapping->addBeforeMiddleware($middleware);
		}
		foreach ($this->getConfig()->get('middleware.' . strtolower($this->serverType) . '.after', []) as $middleware) {
			$this->middlewareMapping->addAfterMiddleware($middleware);
		}
	}

	public function setServerType($type) {
		$this->serverType = $type;
	}

	public function setRouterDispatcher(RouteDispatcher $routeDispatcher) {
		$this->routerDispatcher = $routeDispatcher;
	}

	public function getMiddlewareMapping() {
		return $this->middlewareMapping;
	}

	public function dispatch(...$params) {
		try {
			/**
			 * @var Request $psr7Request
			 * @var Response $psr7Response
			 */
			$psr7Request = $params[0];
			$psr7Response = $params[1];
			$this->getContext()->setRequest($psr7Request);
			$this->getContext()->setResponse($psr7Response);
			$this->getContext()->setContextDataByKey('server-type', $this->serverType);

			$route = $this->getRoute($psr7Request);
			$this->getEventDispatcher()->dispatch(new RouteMatchedEvent($route, $psr7Request));
			$psr7Request = $psr7Request->withAttribute('route', $route);
			$psr7Request->route = $route;
			$this->getContext()->setRequest($psr7Request);

			$middleWares = $this->middlewareMapping->getRouteMiddleWares($route);
			$middlewareHandler = new MiddlewareHandler($middleWares);
			return $middlewareHandler->handle($psr7Request);
		} catch (\Throwable $e) {
			return $this->getContainer()->singleton(HandlerExceptions::class)->handle($e, $this->serverType);
		}
	}

	protected function getRoute(ServerRequestInterface $request) {
		$httpMethod = $request->getMethod();
		$uri = $request->getUri()->getPath();

		return $this->getRouteByMethodAndUrl($httpMethod, $uri);
	}

	protected function getRouteByMethodAndUrl($httpMethod, $uri) {
		$routeData = $this->routerDispatcher->dispatch($httpMethod, $uri);

		switch ($routeData[0]) {
			case RouteDispatcher::NOT_FOUND:
				throw new RouteNotFoundException('Route not found, ' . $uri, 404);
			case RouteDispatcher::METHOD_NOT_ALLOWED:
				throw new RouteNotAllowException('Route not allowed, ' . $uri . ' with method ' . $httpMethod, 405);
			case RouteDispatcher::FOUND:
				break;
		}

		return new Route(
			$routeData[1]['name'],
			$uri,
			$routeData[1]['module'],
			$routeData[1]['handler'],
			$routeData[2] ?? [],
			$routeData[1]['middleware']['before'] ?? [],
			$routeData[1]['defaults'] ?? []
		);
	}
}
