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
use W7\App;
use W7\Core\Route\RouteMapping;
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\SwooleEvent;
use W7\Http\Message\Base\Cookie;
use W7\Http\Message\Formatter\ResponseFormatterInterface;
use W7\Http\Message\Outputer\FpmResponseOutputer;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;

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

		$response = new Psr7Response();
		$response->setOutputer(new FpmResponseOutputer());
		//$response->setFormatter(iloader()->get(ResponseFormatterInterface::class));

		/**
		 * @var Response $response
		 */
		$response = $this->dispatch(Psr7Request::loadFromFpmRequest(), $response);

		$response = $response->withCookie('test', 'value');
		$response = $response->withCookie(Cookie::create('test1', 'value1'));
		$response->send();
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
