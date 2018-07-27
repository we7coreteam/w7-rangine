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
use w7\HttpRoute\HttpServer;

class Dispather extends DispatcherAbstract {

	public $lastMiddleware = \W7\Http\Middleware\RequestMiddleware::class;

    const ROUTE_CONTEXT_KEY = "http-route";

	public function dispatch(...$params) {
		$request       = $params[0];
        $response      = $params[1];
		$serverContext = $params[2];
		$psr7Request = \w7\Http\Message\Server\Request::loadFromSwooleRequest($request);
        $psr7Response = new \w7\Http\Message\Server\Response($response);
        /**
         * @var Context $contextObj
         */
        $contextObj = iloader()->singleton(Context::class);

		$contextObj->setRequest($psr7Request);
        $contextObj->setResponse($psr7Response);

        //根据router配置，获取到匹配的controller信息

		//获取到全部中间件数据，最后附加Http组件的特定的last中间件，用于处理调用Controller
        /**
         * @var Middleware $middlewarehelper
         */

        $middlewarehelper = iloader()->singleton(Middleware::class);
        $dispather  = $this->getController($psr7Request, $serverContext);
        $middlewares = $middlewarehelper->setLastMiddleware($this->lastMiddleware, $dispather['handler'], $serverContext[Middleware::MIDDLEWARE_MEMORY_TABLE_NAME]);
        unset($dispather['handler']['middlerware_key']);
        $psr7Request = $psr7Request->withAddedHeader("dispather", json_encode($dispather));
        $middlewareHandler = new MiddlewareHandler($middlewares);
        try {
            $response = $middlewareHandler->handle($psr7Request);
        }catch (\Throwable $throwable){
            $response = $contextObj->getResponse()->json($throwable->getMessage(), $throwable->getCode());
        }

        $response->send();
	}
	
    /**
     * 通过route信息，调用具体的Controller
     */
    public function getController(ServerRequestInterface $request, array $serverContext) {
        $httpMethod = $request->getMethod();
        $url        = $request->getUri()->getPath();
        $routeData = $serverContext[static::ROUTE_CONTEXT_KEY];
        $fastRoute = new HttpServer();
        $routeInfo = $fastRoute->dispathByData($httpMethod, $url, $routeData);
        list($controller, $method) = explode("-", $routeInfo['handler']);
        $controllerClassName = "W7\\App\\Controller\\" . ucfirst($controller) . "Controller";
        $dispather["handler"] = [
            "controller_classname" => $controllerClassName,
            "method" => $method,
            'middlerware_key' => $controller,
        ];
        $dispather['funArgs'] = $routeInfo['funArgs'];
        return $dispather;
    }
}