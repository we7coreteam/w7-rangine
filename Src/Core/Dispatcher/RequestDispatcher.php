<?php

namespace W7\Core\Dispatcher;

use W7\App;
use FastRoute\Dispatcher;
use Psr\Http\Message\ServerRequestInterface;
use W7\Core\Exception\DevException;
use W7\Core\Exception\HttpException;
use W7\Core\Exception\ReleaseException;
use W7\Core\Helper\Storage\Context;
use W7\Core\Middleware\MiddlewareHandler;
use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;

class RequestDispatcher extends DispatcherAbstract {
	public function dispatch(...$params) {
		/**
		 * @var Request $psr7Request
		 * @var Response $psr7Response
		 */
		try {
			$psr7Request = $params[0];
			$psr7Response = $params[1];
			$response = $this->exec($psr7Request, $psr7Response);
		} catch (HttpException $throwable) {
			$errorMessage = sprintf('Uncaught Exception %s: "%s" at %s line %s',
				get_class($throwable),
				$throwable->getMessage(),
				$throwable->getFile(),
				$throwable->getLine()
			);
			ilogger()->error($errorMessage, array('exception' => $throwable));

			$response = $throwable->render();
		} finally {
			return $response;
		}
	}

	private function exec($psr7Request, $psr7Response) {
		try {
			$serverContext = App::$server->server->context;

			$contextObj = App::getApp()->getContext();
			$contextObj->setRequest($psr7Request);
			$contextObj->setResponse($psr7Response);

			//根据router配置，获取到匹配的controller信息
			//获取到全部中间件数据，最后附加Http组件的特定的last中间件，用于处理调用Controller
			$route = $this->getRoute($psr7Request, $serverContext[Context::ROUTE_KEY]);
			$psr7Request = $psr7Request->withAttribute('route', $route);

			$middlewares = $this->getMiddleware($route, $serverContext[Context::MIDDLEWARE_KEY]);
			$requestLogContextData  = $this->getRequestLogContextData($route['controller'], $route['method']);
			$contextObj->setContextDataByKey(Context::LOG_REQUEST_KEY, $requestLogContextData);

			$middlewareHandler = new MiddlewareHandler($middlewares);
			return $middlewareHandler->handle($psr7Request);
		} catch (\Throwable $throwable) {
			if  (!($throwable instanceof HttpException)) {
				$setting = iconfig()->getUserAppConfig('setting');
				if (!empty($setting['development'])) {
					throw new DevException($throwable->getMessage(), $throwable->getCode(), $throwable);
				}
				throw new ReleaseException($throwable->getMessage(), $throwable->getCode(), $throwable);
			}

			throw $throwable;
		}
	}


	private function getRoute(ServerRequestInterface $request, $fastRoute) {
		$httpMethod = $request->getMethod();
		$url = $request->getUri()->getPath();

		$route = $fastRoute->dispatch($httpMethod, $url);

		$controller = $method = '';
		switch ($route[0]) {
			case Dispatcher::NOT_FOUND:
				throw new HttpException('Route not found', 404);
				break;
			case Dispatcher::METHOD_NOT_ALLOWED:
				throw new HttpException('Route not allowed', 405);
				break;
			case Dispatcher::FOUND:
				if ($route[1]['handler'] instanceof \Closure) {
					$controller = $route[1]['handler'];
					$method = '';
				} else {
					list($controller, $method) = $route[1]['handler'];
				}
				break;
		}

		return [
			"method" => $method,
			'controller' => $controller,
			'args' => $route[2],
			'middleware' => $route[1]['middleware']['before'],
		];
	}

	private function getMiddleware($route, $lastMiddleware) {
		$result = $route['middleware'];
		array_push($result, $lastMiddleware);
		return $result;
	}

	private function getRequestLogContextData($controller, $method) {
		$contextData = [
			'controller' => $controller,
			'method' => $method,
			'requestTime' => microtime(true),
		];
		return $contextData;
	}
}