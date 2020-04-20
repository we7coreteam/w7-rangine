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

namespace W7\Core\Middleware;

use W7\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\Core\Helper\StringHelper;
use W7\Http\Message\Server\Response;

class ControllerMiddleware extends MiddlewareAbstract {
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		//此处处理调用控制器操作
		$route = $request->getAttribute('route');

		//非闭包函数时实列化对象
		if ($route['controller'] instanceof \Closure) {
			$controllerHandler = $route['controller'];
		} else {
			$method = lcfirst(StringHelper::studly($route['method']));
			$classObj = icontainer()->singleton($route['controller']);
			$controllerHandler = [$classObj, $method];
		}

		$funArgs = [];
		$funArgs[] = $request;
		if (is_array($route['args'])) {
			$funArgs = array_merge($funArgs, $route['args']);
		}

		$response = call_user_func_array($controllerHandler, $funArgs);
		App::getApp()->getContext()->setResponse($this->parseResponse($response));

		return $handler->handle($request);
	}

	protected function parseResponse($response) {
		//如果结果是一个response对象，则直接输出，否则按json输出
		if ($response instanceof Response) {
			return $response;
		} elseif (is_object($response)) {
			$response = 'Illegal type ' . get_class($response) . ', Must be a response object, an array, or a string';
		}

		return App::getApp()->getContext()->getResponse()->json($response);
	}
}
