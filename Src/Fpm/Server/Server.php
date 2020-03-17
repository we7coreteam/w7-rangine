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

use FastRoute\Dispatcher\GroupCountBased;
use W7\Core\Route\RouteMapping;
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Fpm\Session\Provider\SessionProvider;
use W7\Http\Message\Server\Request;
use W7\Http\Message\Outputer\FpmResponseOutputer;
use W7\Http\Message\Server\Response as Psr7Response;

class Server extends ServerAbstract {
	protected $providerMap = [
		SessionProvider::class
	];
	public $worker_id;

	public function getType() {
		return ServerEnum::TYPE_FPM;
	}

	protected function registerServerEvent($server) {
		/**
		 * @var ServerEvent $eventRegister
		 */
		$eventRegister = iloader()->get(ServerEvent::class);
		$eventRegister->registerServerUserEvent();
		$eventRegister->registerServerCustomEvent($this->getType());
	}

	public function start() {
		$this->registerService();

		ievent(ServerEvent::ON_USER_BEFORE_START, [$this]);

		$response = new Psr7Response();
		$response->setOutputer(new FpmResponseOutputer());

		$response = $this->dispatch(Request::loadFromFpmRequest(), $response);
		$response->send();
	}

	public function getServer() {
		if (!$this->server) {
			$this->server = $this;
		}
		return $this->server;
	}

	/**
	 * 分发请求
	 * @param $request
	 * @param $response
	 * @return \Psr\Http\Message\ResponseInterface|void
	 */
	private function dispatch($request, $response) {
		$routeInfo = iloader()->get(RouteMapping::class)->getMapping();
		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = \iloader()->get(Dispatcher::class);
		$dispatcher->setRouter(new GroupCountBased($routeInfo));

		ievent(ServerEvent::ON_USER_BEFORE_REQUEST, [$request, $response]);

		$response = $dispatcher->dispatch($request, $response);

		ievent(ServerEvent::ON_USER_AFTER_REQUEST);

		return $response;
	}
}
