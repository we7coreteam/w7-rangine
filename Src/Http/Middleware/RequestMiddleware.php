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
use W7\Core\Base\MiddlewareAbstract;
use W7\Core\Helper\Context;
use W7\Http\Server\Dispather;
use w7\HttpRoute\Exception\BadRequestException;
use w7\HttpRoute\Exception\RouteNotFoundException;

class RequestMiddleware extends MiddlewareAbstract {
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		//此处处理调用控制器操作
        try {
            $routeInfo = Dispather::getController($request);
            list($controller, $method) = explode("-", $routeInfo['handler']);
            $controller = "W7\\App\\Controller\\" . ucfirst($controller) . "Controller";
            $response =  call_user_func_array([$controller, $method], $routeInfo['funArgs']);
            $response = is_array($response)?$response:(array)$response;
            $response = Context::getResponse()->json($response);
            return $response;
        }catch (RouteNotFoundException $routeNotFoundException){
            throw new BadRequestException($routeNotFoundException->getMessage(), 403);
        }catch (\Throwable $throwable){
            throw new BadRequestException($throwable->getMessage(), $throwable->getCode());
        }

	}
}