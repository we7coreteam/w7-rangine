<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 18-7-25
 * Time: 上午10:43
 */

namespace W7\Core\Helper;


class RouteData
{

    const CONTEXT_ROUTE_CONFIG_KEY = "route-config";


    /**
     * @return array
     */
    public function middlerWareData()
    {
        $middlerwares = [];
        $configData = $this->getConfig();

        foreach ($configData as $controller => $route)
        {
            if (isset($route['common']) && !empty($route['common']))
            {
                $middlerwares['controller_midllerware'][$controller] = $route['common'];
            }
            $middlerwares['method_middlerware'] = $this->methodMiddlerWare($route, $controller);

        }
        return $middlerwares;
    }

    /**
     * @param $routeData
     * @param $controller
     * @return array
     */
    protected function methodMiddlerWare($routeData, $controller)
    {
        $methodMiddlewares = [];
        foreach ($routeData as $method=>$data)
        {
            if (isset($data['middleware']) && !empty($data['middleware']))
            {
                $methodMiddlewares[$controller . '_' . $method] = $data['middleware'];
            }
        }
        return $methodMiddlewares;
    }

    /**
     * @return mixed
     */
    protected function getConfig()
    {
        $configData = iconfig()->getUserConfig("route");
        /**
         * @var Context $contextOBj
         */
        $contextOBj = iloader()->singleton(Context::class);
        if ($configData === true){

            $contextOBj->getContextDataByKey(static::CONTEXT_ROUTE_CONFIG_KEY);
            return $configData;
        }
       $contextOBj->setContextDataByKey(static::CONTEXT_ROUTE_CONFIG_KEY, $configData);
        return $configData;
    }

    /**
     * @return array|mixed
     */
    public  function routeData()
    {
        $routes = [];
        $configData = $this->getConfig();
        foreach ($configData as $controller => $route)
        {
            $routes = $this->methodRouteData($route, $controller, $routes);
        }
        return $routes;
    }

    protected function methodRouteData($routeData, $controller, $routes)
    {
        foreach ($routeData as $method=>$data)
        {
            if (isset($data['method']) && !empty($data['method']))
            {
                if (!strstr($data['method'], ',')) {
                    $routes[$data['method']][$controller. '-' . $method] = DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $method . DIRECTORY_SEPARATOR . $data['query'];
                }else
                {
                    $httpMethod = [];
                    $httpMethod = explode(',', $data['method']);
                    $routes[$httpMethod[0]][$controller. '-' . $method] = DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $method . DIRECTORY_SEPARATOR . $data['query'];
                    $routes[$httpMethod[1]][$controller. '-' . $method] = DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $method . DIRECTORY_SEPARATOR . $data['query'];
                }
            }
        }
        return $routes;
    }
}