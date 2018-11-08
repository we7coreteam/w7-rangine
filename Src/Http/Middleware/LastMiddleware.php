<?php
/**
 *
 * @author donknap
 * @date 18-7-24 下午7:45
 */

namespace W7\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\App;
use W7\Core\Middleware\MiddlewareAbstract;

class LastMiddleware extends MiddlewareAbstract
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		//此处处理调用控制器操作
		try {
			$route = $request->getHeader('route');
			$route = json_decode($route[0], true);
			$classObj = iloader()->singleton($route['classname']);
			$controllerHandler = [$classObj, $route['method']];

			$funArgs = [];
			$funArgs[] = $request;
			if (is_array($route['args'])) {
				$funArgs = array_merge($funArgs, $route['args']);
			}

			$response =  call_user_func_array($controllerHandler, $funArgs);
			$response = is_array($response) ? $response : $response;

			$contextObj = App::getApp()->getContext();
			if (is_string($response)) {
				return $contextObj->getResponse()->raw($response);
			} else {
				return $contextObj->getResponse()->json($response);
			}
		} catch (\Throwable $throwable) {
			throw $throwable;
		}
	}
}
