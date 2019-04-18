<?php
/**
 * @author donknap
 * @date 18-7-25 上午10:46
 */
namespace W7\Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\App;
use W7\Core\Helper\StringHelper;

abstract class MiddlewareAbstract implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        //此处处理调用控制器操作
        try {
            $route = $request->getHeader('route');
            $route = json_decode($route[0], true);

            $classname = "W7\\App\\Controller\\" . StringHelper::studly($route['controller']) . "Controller";
            $method = StringHelper::studly($route['method']);

            $classObj = iloader()->singleton($classname);
            $controllerHandler = [$classObj, $method];

            $funArgs = [];
            $funArgs[] = $request;
            if (is_array($route['args'])) {
                $funArgs = array_merge($funArgs, $route['args']);
            }

            $response = $classObj->before($request);
            if ($response === true) {
                $response =  call_user_func_array($controllerHandler, $funArgs);
                if ($response instanceof ResponseInterface) {
                    return $response;
                }
            }
            $response = $classObj->after($response);

            if (is_object($response)) {
                $response = 'Illegal type ' . get_class($response) . ', Must be a response object, an array, or a string';
            } elseif (is_array($response)) {

            } else {
                $response = strval($response);
            }

            $contextObj = App::getApp()->getContext();
//
            return $contextObj->getResponse()->withContent($response);

        } catch (\Throwable $throwable) {
            throw $throwable;
        }
    }
}
