<?php
/**
 * @author donknap
 * @date 18-7-24 下午5:31
 */

namespace W7\Http\Server;

use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use Psr\Http\Message\ServerRequestInterface;
use W7\Core\Dispatcher\DispatcherAbstract;
use W7\Core\Exception\HttpException;
use W7\Core\Middleware\MiddlewareHandler;
use W7\Core\Helper\Context;
use W7\Core\Log\LogHelper;

class Dispather extends DispatcherAbstract {

	public function dispatch(...$params) {
		/**
		 * @var LogHelper $logHelper
		 */
		$logHelper = iloader()->singleton(LogHelper::class);

		$request = $params[0];
		$response = $params[1];
		/**
		 * @var Context $serverContext
		 */
		$serverContext = $params[2];
		$psr7Request = \w7\Http\Message\Server\Request::loadFromSwooleRequest($request);
		$psr7Response = new \w7\Http\Message\Server\Response($response);
		/**
		 * @var Context $contextObj
		 */

		$contextObj = iloader()->singleton(Context::class);
		$contextObj->setRequest($psr7Request);
		$contextObj->setResponse($psr7Response);

		try {
			//根据router配置，获取到匹配的controller信息
			//获取到全部中间件数据，最后附加Http组件的特定的last中间件，用于处理调用Controller
			$route = $this->getRoute($psr7Request, $serverContext[Context::ROUTE_KEY]);
			$psr7Request = $psr7Request->withAddedHeader("route", json_encode($route));

			$middlewares = $this->getMiddleware($serverContext[Context::MIDDLEWARE_KEY], $route['controller'], $route['method']);

			$requestLogContextData  = $this->getRequestLogContextData($route['controller'], $route['method']);
			$contextObj->setContextDataByKey(Context::LOG_REQUEST_KEY, $requestLogContextData);

			$logHelper->addContextInfo($contextObj->getContextDataByKey('workid'), '', $contextObj->getContextDataByKey('coid'), $route['controller'], $route['method']);

			$middlewareHandler = new MiddlewareHandler($middlewares);
			$response = $middlewareHandler->handle($psr7Request);

		} catch (\Throwable $throwable) {
			$logHelper->exceptionHandler($throwable);
			$response = $contextObj->getResponse()->json($throwable->getMessage(), $throwable->getCode());
		}

		//ievent('afterRequest');
		$response->send();
	}


	private function getRoute(ServerRequestInterface $request, $routeInfo) {
		$httpMethod = $request->getMethod();
		$url = $request->getUri()->getPath();

		/**
		 * @var GroupCountBased $fastRoute
		 */
		$fastRoute = new GroupCountBased($routeInfo);
		$route = $fastRoute->dispatch($httpMethod, $url);

		switch ($route[0]) {
			case Dispatcher::NOT_FOUND:
				throw new HttpException('Route not found', 404);
				break;
			case Dispatcher::METHOD_NOT_ALLOWED:
				throw new HttpException('Route not allowed', 405);
				break;
			case Dispatcher::FOUND:
				$controller = $method = '';
				list($controller, $method) = explode("-", $route[1]);
				break;
		}

		return [
			"method" => $method,
			'controller' => $controller,
			'classname' => "W7\\App\\Controller\\" . ucfirst($controller) . "Controller",
			'args' => $route[2],
		];
	}

	private function getMiddleware($allMiddleware, $controller, $action) {
		$result = [];
		$controllerMiddlerwares = !empty($allMiddleware[$controller]) ? $allMiddleware[$controller] : [];

		foreach ($controllerMiddlerwares as $method => $middlerware) {
			if (strstr($method, $action) || $method == "default") {
				$result = array_merge($result, $controllerMiddlerwares[$method]);
			}
		}

		//附加最后中间件
		if (!empty($allMiddleware['last'])) {
			$result = array_merge($result, $allMiddleware['last']);
		}

		return $result;
	}

	private function getRequestLogContextData($controller, $method) {
		$contextData = [
			'controller'=>$controller,
			'method'=>$method,
			'requestTime' => microtime(true),
		];
		return $contextData;
	}
}
