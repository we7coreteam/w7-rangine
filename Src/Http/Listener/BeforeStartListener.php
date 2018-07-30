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

class BeforeStartListener implements ListenerInterface
{
    public function run()
    {
        $this->addRoute();
        /**
         * @var Middleware $middlerwareObj
         */
        $middlerwareObj = iloader()->singleton(Middleware::class);
        $middlerwares   = $middlerwareObj->getMiddlewares();
        /**
         * @var Context $contextObj
         */
        $contextObj = iloader()->singleton(Context::class);
        $contextObj->setContextDataByKey(Context::MIDDLEWARE_KEY, $middlerwares);
    }

    private function addRoute()
    {
        $routeList = [];
        /**
         * @var RouteData $routeObj
         */
        $routeObj  = iloader()->singleton(RouteData::class);
        $configData = $routeObj->routeData();
        $fastRoute = new HttpServer();
        foreach ($configData as $httpMethod=>$routeData) {
            $routeList = array_merge_recursive($routeList, $fastRoute->addRoute($httpMethod, $routeData));
        }
        /**
         * @var Context $contextObj
         */
        $contextObj = iloader()->singleton(Context::class);
        $contextObj->setContextDataByKey(Context::ROUTE_KEY, $routeList);
    }
}
