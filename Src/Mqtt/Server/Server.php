<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Mqtt\Server;

use W7\Core\Process\ProcessServerAbstract;
use W7\Core\Route\RouteDispatcher;
use W7\Core\Route\Router;
use W7\Core\Server\ServerEnum;
use W7\Mqtt\Listener\SubscribeListener;
use W7\Core\Route\RouteMapping;

class Server extends ProcessServerAbstract {
	protected function register() {
		$routeMapping = RouteDispatcher::getRouteDefinetions(RouteMapping::class, ServerEnum::TYPE_MQTT);

		if (!empty($routeMapping[0][Router::METHOD_MQTT_TOPIC])) {
			/**
			 * @var Dispatcher $dispatcher
			 */
			$dispatcher = $this->getContainer()->get(Dispatcher::class);
			$dispatcher->setRouterDispatcher(new RouteDispatcher($routeMapping));

			foreach ($routeMapping[0][Router::METHOD_MQTT_TOPIC] as $uri => $handler) {
				// 遍历路由，找到 topic 添加 process
				$this->pool->registerProcess($uri, SubscribeListener::class, $process['worker_num'] ?? 1);
			}
		}
	}

	public function getType() {
		return ServerEnum::TYPE_MQTT;
	}
}
