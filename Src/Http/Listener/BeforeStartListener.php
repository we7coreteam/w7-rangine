<?php
/**
 * 开启服务之前，先构造中间件数据及路由数据
 * @author donknap
 * @date 18-7-25 下午4:51
 */

namespace W7\Http\Listener;

use W7\App;
use W7\Core\Listener\ListenerInterface;
use W7\Core\Middleware\MiddlewareMapping;
use W7\Core\Route\RouteMapping;
use W7\Core\Helper\Storage\Context;

class BeforeStartListener implements ListenerInterface
{
	public function run() {

		$context = App::getApp()->getContext();
		$context->setContextDataByKey(Context::ROUTE_KEY, $this->getRoute());
		$context->setContextDataByKey(Context::MIDDLEWARE_KEY, $this->getMiddleware());

		return true;
	}

	private function getRoute() {
		return iloader()->singleton(RouteMapping::class)->getMapping();
	}

	private function getMiddleware() {
		/**
		 * @var MiddlewareMapping $middlerwareObj
		 */
		$middlerwareObj = iloader()->singleton(MiddlewareMapping::class);
		return $middlerwareObj->getMapping();
	}
}
