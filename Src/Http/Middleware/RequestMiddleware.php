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
use W7\Core\Base\Middleware\MiddlewareAbstract;
use W7\Core\Helper\Context;
use w7\HttpRoute\Exception\BadRequestException;
use w7\HttpRoute\Exception\RouteNotFoundException;

class RequestMiddleware extends MiddlewareAbstract
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //此处处理调用控制器操作
        try {
            $route = $request->getHeader('route');
            $route = json_decode($route[0], true);
            $classObj = iloader()->singleton($route['classname']);
            $controllerHandler = [$classObj, $route['method']];
            $funArgs = $route['args'];
            $funArgs[] = $request;
            $response =  call_user_func_array($controllerHandler, $funArgs);
            $response = is_array($response)?$response:(array)$response;
            /**
             * @var Context $contextObj
             */
            $contextObj = iloader()->singleton(Context::class);
            $response = $contextObj->getResponse()->json($response);
            return $response;
        } catch (RouteNotFoundException $routeNotFoundException) {
            throw new BadRequestException($routeNotFoundException->getMessage(), 403);
        } catch (\Throwable $throwable) {
            throw new BadRequestException($throwable->getMessage(), $throwable->getCode());
        }
    }
}
