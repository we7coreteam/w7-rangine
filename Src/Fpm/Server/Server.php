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

namespace W7\Fpm\Server;

use W7\Core\Route\RouteDispatcher;
use W7\Core\Route\RouteMapping;
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Http\Message\Outputer\FpmResponseOutputer;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;

class Server extends ServerAbstract {
	public $worker_id;

	public function getType() {
		return ServerEnum::TYPE_FPM;
	}

	protected function registerServerEvent($server) {
		/**
		 * @var ServerEvent $eventRegister
		 */
		$eventRegister = $this->getContainer()->get(ServerEvent::class);
		$eventRegister->registerServerUserEvent();
		$eventRegister->registerServerCustomEvent($this->getType());
	}

	public function start() {
		$this->registerService();

		$this->getEventDispatcher()->dispatch(ServerEvent::ON_USER_BEFORE_START, [$this, $this->getType()]);

		$response = new Psr7Response();
		$response->setOutputer(new FpmResponseOutputer());

		$response = $this->dispatch(Psr7Request::loadFromFpmRequest(), $response);
		$response->send();
	}

	public function getServer() {
		if (!$this->server) {
			$this->worker_id = getmypid();
			$this->server = $this;
		}
		return $this->server;
	}

	private function dispatch($request, $response) {
		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = $this->getContainer()->get(Dispatcher::class);
		$dispatcher->setRouterDispatcher(RouteDispatcher::getDispatcherWithRouteMapping(RouteMapping::class, $this->getType()));

		return $dispatcher->dispatch($request, $response);
	}
}
