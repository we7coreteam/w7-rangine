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

use W7\App;
use Psr\Http\Message\ServerRequestInterface;
use W7\Core\Exception\HandlerExceptions;
use W7\Core\Exception\RouteNotAllowException;
use W7\Core\Exception\RouteNotFoundException;
use W7\Core\Middleware\MiddlewareHandler;
use W7\Core\Middleware\MiddlewareMapping;
use W7\Core\Route\Event\RouteMatchedEvent;
use W7\Core\Route\RouteDispatcher;
use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;

class RequestDispatcher extends DispatcherAbstract {
	/**
	 * @var MiddlewareMapping
	 */
	protected $middlewareMapping;
	protected $serverType;
	/**
	 * @var RouteDispatcher
	 */
	protected $routerDispatcher;

	public function __construct() {
		//当不同类型的server一起启动时，需要区分middleware
		$this->serverType = lcfirst(explode('\\', static::class)[1]);
		$this->middlewareMapping = new MiddlewareMapping();
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
			$contextObj = App::getApp()->getContext();
			$contextObj->setRequest($psr7Request);
			$contextObj->setResponse($psr7Response);
			$contextObj->setContextDataByKey('server-type', $this->serverType);

			//根据router配置，获取到匹配的controller信息
			//获取到全部中间件数据，最后附加Http组件的特定的last中间件，用于处理调用Controller
			$route = $this->getRoute($psr7Request);
			ievent(new RouteMatchedEvent($route, $psr7Request));
			$psr7Request = $psr7Request->withAttribute('route', $route);
			$contextObj->setRequest($psr7Request);

			$middleWares = $this->middlewareMapping->getRouteMiddleWares($route);
			$middlewareHandler = new MiddlewareHandler($middleWares);
			return $middlewareHandler->handle($psr7Request);
		} catch (\Throwable $e) {
			return icontainer()->singleton(HandlerExceptions::class)->handle($e, $this->serverType);
		}
	}

	protected function getRoute(ServerRequestInterface $request) {
		$httpMethod = $request->getMethod();
		$url = $request->getUri()->getPath();

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
