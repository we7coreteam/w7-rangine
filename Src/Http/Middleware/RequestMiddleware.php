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

    const ROUTE_CONTEXT_KEY = "http-route";


	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		//此处处理调用控制器操作
        try {
            static::addRoute();
            $dispather = static::getController($request);
            list($controller, $funArgs) = $dispather;
            $response =  call_user_func_array($controller, $funArgs);
            $response = is_array($response)?$response:(array)$response;
            $response = Context::getResponse()->json($response);
            return $response;
        }catch (RouteNotFoundException $routeNotFoundException){
            throw new BadRequestException($routeNotFoundException->getMessage(), 403);
        }catch (\Throwable $throwable){
            throw new BadRequestException($throwable->getMessage(), $throwable->getCode());
        }

	}

    /**
     * 通过route信息，调用具体的Controller
     */
    public static function getController(ServerRequestInterface $request) {
        $httpMethod = $request->getMethod();
        $url        = $request->getUri()->getPath();
        $routeData = Context::getContextDataByKey(static::ROUTE_CONTEXT_KEY);
        $fastRoute = new HttpServer();
        $routeInfo = $fastRoute->dispathByData($httpMethod, $url, $routeData);
        list($controller, $method) = explode("-", $routeInfo['handler']);
        $controller = "W7\\App\\Controller\\" . ucfirst($controller) . "Controller";
        $dispather[0] = [$controller, $method];
        $dispather[1] = $routeInfo['funArgs'];
        return $dispather;
    }



    /**
     *
     */
    public static function addRoute()
    {
        $routeList = [];
        $configData = RouteData::routeData();
        $fastRoute = new HttpServer();
        foreach($configData as $httpMethod=>$routeData)
        {
            $routeList = array_merge_recursive($routeList ,$fastRoute->addRoute($httpMethod, $routeData));
        }
        Context::setContextDataByKey(static::ROUTE_CONTEXT_KEY, $routeList);
    }
}