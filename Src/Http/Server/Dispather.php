<?php
/**
 * @author donknap
 * @date 18-7-24 下午5:31
 */

namespace W7\Http\Server;

use Psr\Http\Message\ServerRequestInterface;
use W7\Core\Base\DispatcherAbstract;
use W7\Core\Base\MiddlewareHandler;
use W7\Core\Helper\Context;
use W7\Core\Helper\Middleware;
use W7\Http\Handler\RouteHandler;
use w7\HttpRoute\HttpServer;

class Dispather extends DispatcherAbstract {

	public $lastMiddleware = \W7\Http\Middleware\RequestMiddleware::class;

	public function dispatch(...$params) {
		list($request, $response) = $params;

		$psr7Request = \w7\Http\Message\Server\Request::loadFromSwooleRequest($request);
        $psr7Response = new \w7\Http\Message\Server\Response($response);

		Context::setRequest($psr7Request);
        Context::setResponse($psr7Response);

        //根据router配置，获取到匹配的controller信息

		//获取到全部中间件数据，最后附加Http组件的特定的last中间件，用于处理调用Controller
        RouteHandler::addRoute();
        $middlewarehelper = new Middleware();
        $table = $middlewarehelper->insertMiddlewareCached();
        $middlewarehelper->setLastMiddleware($this->lastMiddleware);
        $middlewares = $middlewarehelper->getMiddlewares($table);
        $middlewareHandler = new MiddlewareHandler($middlewares);
        try {
            $response = $middlewareHandler->handle($psr7Request);
        }catch (\Throwable $throwable){
            $response = Context::getResponse()->json($throwable->getMessage(), $throwable->getCode());
        }


        $psr7Response->send();
	}

	/**
	 * 通过route信息，调用具体的Controller
	 */
	public static function handler(ServerRequestInterface $request) {
        $httpMethod = $request->getMethod();
        $url        = $request->getUri()->getPath();
        $routeData = Context::getContextDataByKey(RouteHandler::ROUTE_CONTEXT_KEY);
        $fastRoute = new HttpServer();
        $routeInfo = $fastRoute->dispathByData($httpMethod, $url, $routeData);
        return $routeInfo;
	}
}