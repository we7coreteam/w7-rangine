<?php
/**
 * @author donknap
 * @date 18-7-24 下午5:31
 */

namespace W7\Http\Server;

use Psr\Http\Message\ServerRequestInterface;
use W7\Core\Base\DispatcherAbstract;
use W7\Core\Base\Logger;
use W7\Core\Base\MiddlewareHandler;
use W7\Core\Helper\Context;
use W7\Core\Helper\Middleware;
use w7\HttpRoute\HttpServer;

class Dispather extends DispatcherAbstract
{
    public $lastMiddleware = \W7\Http\Middleware\RequestMiddleware::class;


    public function dispatch(...$params)
    {
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
        $route = $this->getRoute($psr7Request, $serverContext[Context::ROUTE_KEY]);
        $psr7Request = $psr7Request->withAddedHeader("route", json_encode($route));

        /**
         * @var Middleware $middlewarehelper
         */
        $middlewarehelper = iloader()->singleton(Middleware::class);
        $middlewarehelper->initMiddleware($serverContext[Context::MIDDLEWARE_KEY]);
        $middlewares = $middlewarehelper->getMiddlewareByRoute($route['controller'], $route['method']);
        Logger::addBasic("logid", uniqid());
        Logger::addBasic("client", getClientIp());
        Logger::addBasic('controller', $route['controller']);
        Logger::addBasic('method',     $route['method']);
        $middlewares = $middlewarehelper->setLastMiddleware($this->lastMiddleware, $middlewares);

        $middlewareHandler = new MiddlewareHandler($middlewares);
        try {
            $response = $middlewareHandler->handle($psr7Request);
        } catch (\Throwable $throwable) {
            $response = $contextObj->getResponse()->json($throwable->getMessage(), $throwable->getCode());
        }

        $response->send();
    }


    private function getRoute(ServerRequestInterface $request, $routeInfo)
    {
        $httpMethod = $request->getMethod();
        $url = $request->getUri()->getPath();

        $fastRoute = new HttpServer();
        $routeInfo = $fastRoute->dispathByData($httpMethod, $url, $routeInfo);

        list($controller, $method) = explode("-", $routeInfo['handler']);

        return [
            "method" => $method,
            'controller' => $controller,
            'classname' => "W7\\App\\Controller\\" . ucfirst($controller) . "Controller",
            'args' => $routeInfo['funArgs'],
        ];
    }
}
