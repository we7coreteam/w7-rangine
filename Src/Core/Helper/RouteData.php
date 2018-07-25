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
    public function middlerWareData()
    {
        $middlerwares = [];
        $configData = iconfig()->getUserConfig("route");
        foreach ($configData as $controller => $route)
        {
            if (isset($route['common']) && empty($route['common']))
            {
                $middlerwares['controller_midllerware'][$controller] = $route['common'];
            }
            $middlerwares['method_middlerware'] = $this->methodMiddlerWare($route, $controller);

        }
        return $middlerwares;
    }

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
}