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
use W7\Core\Helper\RouteData;
use W7\Http\Server\Dispather;
use w7\HttpRoute\Exception\BadRequestException;
use w7\HttpRoute\Exception\RouteNotFoundException;
use w7\HttpRoute\HttpServer;

class RequestMiddleware extends MiddlewareAbstract {

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		//此处处理调用控制器操作
        try {
            $dispather = $request->getHeader('dispather');
            $dispather = json_decode($dispather[0], true);
            $controllerHandler = array_values($dispather['handler']);
            $funArgs = $dispather['funArgs'];
            $response =  call_user_func_array($controllerHandler, $funArgs);
            $response = is_array($response)?$response:(array)$response;
            /**
             * @var Context $contextObj
             */
            $contextObj = iloader()->singleton(Context::class);
            $response = $contextObj->getResponse()->json($response);
            return $response;
        }catch (RouteNotFoundException $routeNotFoundException){
            throw new BadRequestException($routeNotFoundException->getMessage(), 403);
        }catch (\Throwable $throwable){
            throw new BadRequestException($throwable->getMessage(), $throwable->getCode());
        }

	}



}