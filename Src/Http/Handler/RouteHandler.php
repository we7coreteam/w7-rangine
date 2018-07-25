<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 18-7-25
 * Time: 下午12:40
 */

namespace W7\Http\Handler;


use W7\Core\Helper\Context;
use W7\Core\Helper\RouteData;
use w7\Http\Message\Server\Request;
use w7\HttpRoute\HttpServer;

class RouteHandler
{

    const ROUTE_CONTEXT_KEY = "http-route";


    /**
     *
     */
    public function addRoute()
    {
        $routeList = [];
        $configData = RouteData::routeData();
        $fastRoute = new HttpServer();
        foreach($configData as $httpMethod=>$routeData)
        {
            $routeList[] = $fastRoute->addRoute($httpMethod, $routeData);
        }
        Context::setContextDataByKey(static::ROUTE_CONTEXT_KEY, $routeList);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function dispath(Request $request)
    {
        $httpMethod = $request->getMethod();
        $url        = $request->getUri();
        $routeData = Context::getContextDataByKey(static::ROUTE_CONTEXT_KEY);
        $fastRoute = new HttpServer();
        $routeInfo = $fastRoute->dispathByData($httpMethod, $url, $routeData);
        return $routeInfo;
    }
}