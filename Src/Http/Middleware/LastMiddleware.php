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
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
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
			ilogger()->info(var_export($response, true));

			//如果结果是一个response对象，则直接输出，否则按json输出
			if ($response instanceof ResponseInterface) {
				ilogger()->info(var_export($response, true));
				return $response;
			}

			$contextObj = App::getApp()->getContext();
			return $contextObj->getResponse()->json($response);

		} catch (\Throwable $throwable) {
			throw $throwable;
		}
	}
}
