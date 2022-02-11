<?php

namespace W7\Tests;

use FastRoute\Dispatcher\GroupCountBased;
use Illuminate\Filesystem\Filesystem;
use W7\App;
use W7\Core\Controller\ControllerAbstract;
use W7\Core\Controller\FaviconController;
use W7\Core\Dispatcher\RequestDispatcher;
use W7\Core\Exception\RouteNotAllowException;
use W7\Core\Exception\RouteNotFoundException;
use W7\Core\Facades\Router;
use W7\Core\Helper\FileLoader;
use W7\Core\Middleware\ControllerMiddleware;
use W7\Core\Middleware\MiddlewareAbstract;
use W7\Core\Middleware\MiddlewareHandler;
use W7\Core\Route\Route;
use W7\Core\Route\RouteDispatcher;
use W7\Core\Route\RouteMapping;
use W7\Facade\Context;
use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;
use W7\Http\Server\Dispatcher;
use W7\Http\Server\Server;

class TestController extends ControllerAbstract {
	public function index(Request $request) {
		return $this->responseJson('test');
	}
}

class DispatcherMiddleware extends MiddlewareAbstract {

}

class Dispatcher1Middleware extends MiddlewareAbstract {

}

class RequestDispatcherTest extends TestCase {
	public function testDispatcher() {
		$dispatcher = new Dispatcher();
		\W7\Facade\Router::middleware([
			DispatcherMiddleware::class,
			Dispatcher1Middleware::class
		])->get('/test_dispatcher', function () {
			return 1;
		});

		$dispatcher->setRouterDispatcher(RouteDispatcher::getDispatcherWithRouteMapping(RouteMapping::class, 'http'));

		$request = new Request('GET', '/test_dispatcher');
		Context::setResponse(new Response());

		$reflect = new \ReflectionClass($dispatcher);
		$method = $reflect->getMethod('getRoute');
		$method->setAccessible(true);
		/**
		 * @var Route $route
		 */
		$route = $method->invoke($dispatcher, $request);

		$middleWares = $dispatcher->getMiddlewareMapping()->getRouteMiddleWares($route, 'http');
		$this->assertSame(DispatcherMiddleware::class, $middleWares[0]['class']);
		$this->assertSame(Dispatcher1Middleware::class, $middleWares[1]['class']);

		$request->route = $route;
		$middlewareHandler = new MiddlewareHandler($middleWares);
		$response = $middlewareHandler->handle($request);
		$this->assertSame('{"data":1}', $response->getBody()->getContents());
	}

	public function testResponseJson() {
		$dispatcher = new Dispatcher();
		\W7\Facade\Router::get('/json-response', ['\W7\Tests\TestController', 'index']);

		$dispatcher->setRouterDispatcher(RouteDispatcher::getDispatcherWithRouteMapping(RouteMapping::class, 'http'));

		$request = new Request('GET', '/json-response');
		$response = new Response();
		$response = $dispatcher->dispatch($request, $response);
		$this->assertSame('{"data":"test"}', $response->getBody()->getContents());
	}

	public function testIgnoreRoute() {
		$dispatcher = new Dispatcher();
		$dispatcher->setRouterDispatcher(RouteDispatcher::getDispatcherWithRouteMapping(RouteMapping::class, 'http'));

		$request = new Request('GET', '/favicon.ico');

		$reflect = new \ReflectionClass($dispatcher);
		$method = $reflect->getMethod('getRoute');
		$method->setAccessible(true);
		/**
		 * @var Route $route
		 */
		$route = $method->invoke($dispatcher, $request);

		$this->assertSame($route->getController(), '\W7\Core\Controller\FaviconController');
		$this->assertSame('system', $route->getModule());
	}

	public function testNotFound() {
		$dispatcher = new Dispatcher();
		$dispatcher->setRouterDispatcher(RouteDispatcher::getDispatcherWithRouteMapping(RouteMapping::class, 'http'));

		$request = new Request('POST', '/post');

		$reflect = new \ReflectionClass($dispatcher);
		$method = $reflect->getMethod('getRoute');
		$method->setAccessible(true);

		try {
			$method->invoke($dispatcher, $request);
		} catch (\Throwable $e) {
			$this->assertInstanceOf(RouteNotFoundException::class, $e);
			$this->assertSame('{"error":"Route not found, \/post"}', $e->getMessage());
		}
	}

	public function testNotAllow() {
		$dispatcher = new Dispatcher();
		$dispatcher->setRouterDispatcher(RouteDispatcher::getDispatcherWithRouteMapping(RouteMapping::class, 'http'));

		$request = new Request('POST', '/favicon.ico');

		$reflect = new \ReflectionClass($dispatcher);
		$method = $reflect->getMethod('getRoute');
		$method->setAccessible(true);

		try {
			$method->invoke($dispatcher, $request);
		} catch (\Throwable $e) {
			$this->assertInstanceOf(RouteNotAllowException::class, $e);
			$this->assertSame('{"error":"Route not allowed, \/favicon.ico with method POST"}', $e->getMessage());
		}
	}
}