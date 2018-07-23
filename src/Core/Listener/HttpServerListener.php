<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:08
 */

namespace W7\Core\Listener;

use Swoole\Http\Request;
use Swoole\Http\Response;
use W7\Http\Handler\AdapterHandler;
use w7\Http\Handler\RequestHandler;
use w7\Http\Middleware\MiddlewareProcessor;
use w7\HttpRoute\HttpServer;

class HttpServerListener {
    /**
     * @param Request $request
     * @param Response $response
     * @return \Psr\Http\Message\ResponseInterface|Response
     * @throws \ReflectionException
     */
	public function onRequest(Request $request, Response $response) {
	    $routeObj    = new HttpServer();
	    $middleObj   = new MiddlewareProcessor();
	    $routes      = iconfig()->getUserConfig("Routes");
	    $middlewares = iconfig()->getUserConfig("Middlewares");
	    $routeTableObj    = $routeObj->addRoutDataInCache($routes);
	    $middlewaresTableObj = $middleObj->insertMiddlewareCached($middlewares, MiddlewareProcessor::MEMORY_CACHE_TYPE);
        $middlewaresConf = $middleObj->getMiddlewares(MiddlewareProcessor::MEMORY_CACHE_TYPE, "", $middlewaresTableObj);
        $httpMethod  = $request->server['request_method'];
        $url         = $request->server['request_uri'];
        $handleArray = $routeObj->dispatch($httpMethod, $url, $routeTableObj);
        $psr7Request = \w7\Http\Message\Server\Request::loadFromSwooleRequest($request);
        $requestHandler = new RequestHandler($middlewaresConf, '');
        $requestHandler->handle($psr7Request);
        $handlerAdapter = new AdapterHandler();
        $response =  $handlerAdapter->doHandler($psr7Request, $handleArray['handler'], $handleArray['funArgs']);
        $response->send();
	}

}