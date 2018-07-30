<?php
/**
 * 保存中间件数据
 * @author alex
 * @date 18-7-24 下午5:31
 */

namespace W7\Core\Helper;

class Middleware
{
    protected $middlewares;


    public function initMiddleware(array $middleware)
    {
        $this->middlewares = $middleware;
    }

    public function getMiddlewareByRoute(string $routeController, string $routeMethod)
    {
        $result = [];
        $controllerMiddlerwares = !empty($this->middlewares[$routeController])?$this->middlewares[$routeController]:[];
        foreach ($controllerMiddlerwares as $method => $middlerware) {
            if (strstr($method, $routeMethod)|| $method == "default") {
                $result = array_merge($result, $controllerMiddlerwares[$method]);
            }
        }
        return $result;
    }
    /**
     * @param string $middlerware
     * @param array $dispather
     * @return array
     */
    public function setLastMiddleware(string $lasteMiddlerware, array $middlewares)
    {
        array_unshift($middlewares, $lasteMiddlerware);
        return $middlewares;
    }
    /**
     * @param int $cacheType
     * @param string|null $filePath
     * @param Table|null $tableObj
     */
    public function getMiddlewares()
    {
        $dataHepler  = new RouteData();
        $middlewares = $dataHepler->middlerWareData();
        $middlewares = $this->formatData($middlewares['controller_midllerware'], $middlewares['method_middlerware']);
        $systemMiddlerwares = iconfig()->getUserConfig("define");
        $beforeMiddlerwares = !empty($systemMiddlerwares['middlerware']['befor_middlerware'])?$systemMiddlerwares['middlerware']['befor_middlerware']:[];
        $afterMiddlerwares  = !empty($systemMiddlerwares['middlerware']['after_middlerware'])?$systemMiddlerwares['middlerware']['after_middlerware']:[];
        $middlewares = array_merge($beforeMiddlerwares, $middlewares, $afterMiddlerwares);
        return $middlewares;
    }

    /**
     * @param array $commonMiddlewares
     * @param array $methodMiddlewares
     * @return array
     */
    protected function formatData(array $commonMiddlewares, array $methodMiddlewares)
    {
        $middlewares = [];
        foreach ($commonMiddlewares as $controller=>$middleware) {
            if (empty($middleware)) {
                continue;
            }
            $middlewares[$controller]['default'] = $middleware;
        }
        foreach ($methodMiddlewares as $method=>$middleware) {
            if (empty($middleware)) {
                continue;
            }
            $middlewares[$controller][$method] = $middleware;
        }
        return $middlewares;
    }
}
