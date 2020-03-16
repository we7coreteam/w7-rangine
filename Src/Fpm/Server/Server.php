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
use Symfony\Component\HttpFoundation\Response;
use W7\Core\Route\RouteMapping;
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Http\Message\Server\Request;

class Server extends ServerAbstract {
	public $worker_id;

	public function getType() {
		return ServerEnum::TYPE_FPM;
	}

	protected function registerServerEventListener() {
		$eventTypes = [$this->getType()];
		iloader()->get(ServerEvent::class)->register($eventTypes);
	}

	public function start() {
		$this->registerService();
		ievent(ServerEvent::ON_USER_BEFORE_START, [$this]);

		$this->dispatch(Request::loadFromFpmRequest(), new \W7\Http\Message\Server\Response());
	}

	public function getServer() {
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
		$response = $dispatcher->dispatch($request, $response);

		$symfonyResponse = Response::create();
		$symfonyResponse->setStatusCode($response->getStatusCode());
		$symfonyResponse->setContent($response->getBody()->getContents());
		$symfonyResponse->headers->add($response->getHeaders());

		$symfonyResponse->send();
	}
}
