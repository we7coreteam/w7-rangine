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
use W7\Http\Handler\RouteHandler;
use w7\Http\Message\Server\Request;
use w7\HttpRoute\Exception\BadRequestException;
use w7\HttpRoute\Exception\RouteNotFoundException;

class RequestMiddleware extends MiddlewareAbstract {
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		//此处处理调用控制器操作
        try {
            $routeInfo = RouteHandler::dispath($request);
            list($controller, $method) = explode("-", $routeInfo['handler']);
            $controllerObj = new $controller();
            return $controller->$method;
        }catch (RouteNotFoundException $routeNotFoundException){
            throw new BadRequestException($routeNotFoundException->getMessage(), 403);
        }catch (\Throwable $throwable){
            throw new BadRequestException($throwable->getMessage(), $throwable->getCode());
        }

	}
}