<?php
/**
 * 开启服务之前，先构造中间件数据及路由数据
 * @author donknap
 * @date 18-7-25 下午4:51
 */

namespace W7\Tcp\Listener;

use Thrift\TMultiplexedProcessor;
use W7\App;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Middleware\MiddlewareMapping;
use W7\Core\Route\RouteMapping;
use W7\Core\Helper\Storage\Context;
use FastRoute\Dispatcher\GroupCountBased;

class BeforeStartListener extends ListenerAbstract {
	public function run(...$params) {
		$context = App::getApp()->getContext();
		$context->setContextDataByKey(Context::ROUTE_KEY, $this->getRoute());
		$context->setContextDataByKey(Context::MIDDLEWARE_KEY, $this->getMiddleware());

		$this->registerServices();

		return true;
	}

    private function registerServices() {
        $process = new TMultiplexedProcessor();
        /**
         * system service
         */
        $services = [
            'Dispatcher' => [
                'handle' => '\\W7\\Tcp\\Services\\Dispatcher\\DispatcherHandle',
                'process' => '\\W7\\Tcp\\Services\\Dispatcher\\DispatcherProcessor'
            ]
        ];

        /**
         * register user services
         */

        foreach ($services as $key => $value) {
            $serviceHandler = new $value['handle']();
            $serviceProcess = new $value['process']($serviceHandler);
            $process->registerProcessor($key, $serviceProcess);
        }

        $context = App::getApp()->getContext();
        $context->setContextDataByKey('thrift_process', $process);
    }

	/**
	 * @return GroupCountBased
	 */
	private function getRoute() {
		$routeInfo = iloader()->singleton(RouteMapping::class)->getMapping();
		return new GroupCountBased($routeInfo);
	}

	private function getMiddleware() {
		/**
		 * @var MiddlewareMapping $middlerwareObj
		 */
		$middlerwareObj = iloader()->singleton(MiddlewareMapping::class);
		return $middlerwareObj->getMapping();
	}
}
