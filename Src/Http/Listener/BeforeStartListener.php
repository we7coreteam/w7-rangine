<?php
/**
 * @author donknap
 * @date 18-7-25 下午4:51
 */

namespace W7\Http\Listener;

use W7\Core\Base\ListenerInterface;
use W7\Core\Helper\Context;
use W7\Core\Helper\Middleware;
use W7\Core\Helper\RouteData;
use W7\Http\Server\Dispather;
use w7\HttpRoute\HttpServer;

class BeforeStartListener implements ListenerInterface {
	public function run() {
	    echo 1111111;
	    static::addRoute();
	    Middleware::insertMiddlewareCached();
	}

    private static function addRoute()
    {
        $routeList = [];
        $configData = RouteData::routeData();
        $fastRoute = new HttpServer();
        foreach($configData as $httpMethod=>$routeData)
        {
            $routeList = array_merge_recursive($routeList ,$fastRoute->addRoute($httpMethod, $routeData));
        }
        /**
         * @var Context $contextObj
         */
        $contextObj = iloader()->singleton(Context::class);
        $contextObj->setContextDataByKey(Dispather::ROUTE_CONTEXT_KEY, $routeList);
    }
}