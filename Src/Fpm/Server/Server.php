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
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use W7\App;
use W7\Core\Route\RouteMapping;
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\SwooleEvent;
use W7\Http\Message\Server\Request;

class Server extends ServerAbstract {
	public static $masterServer = false;
	public $worker_id;

	public function __construct() {
		!App::$server && App::$server = $this;
		$this->server = $this;
		$this->worker_id = getmypid();
	}

	public function getType() {
		return ServerEnum::TYPE_FPM;
	}

	protected function registerServerEventListener() {
		$eventTypes = [$this->getType()];
		iloader()->get(SwooleEvent::class)->register($eventTypes);
	}

	public function start() {
		//$this->registerService();
		ievent(SwooleEvent::ON_USER_BEFORE_START, [$this]);

		$response = $this->dispatch(Request::loadFromFpmRequest(), new \W7\Http\Message\Server\Response());

		$symfonyResponse = Response::create();
		$symfonyResponse->setStatusCode($response->getStatusCode());
		$symfonyResponse->setContent($response->getBody()->getContents());
		$symfonyResponse->headers->add($response->getHeaders());

		$symfonyResponse->send();
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
		return $dispatcher->dispatch($request, $response);
	}
}
