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

namespace W7\Http\Listener;

use W7\App;
use Swoole\Coroutine;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\SwooleEvent;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;
use W7\Http\Server\Dispatcher;

class RequestListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $request, $response) = $params;
		return $this->dispatch($server, $request, $response);
	}

	/**
	 * @param Server $server
	 * @param Request $request
	 * @param Response $response
	 * @throws \Exception
	 */
	private function dispatch(Server $server, Request $request, Response $response) {
		ievent(SwooleEvent::ON_USER_BEFORE_REQUEST);

		$context = App::getApp()->getContext();
		$context->setContextDataByKey('workid', $server->worker_id);
		$context->setContextDataByKey('coid', Coroutine::getuid());

		$psr7Request = Psr7Request::loadFromSwooleRequest($request);
		$psr7Response = Psr7Response::loadFromSwooleResponse($response);

		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = \iloader()->singleton(Dispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);
		$psr7Response->send();

		ievent(SwooleEvent::ON_USER_AFTER_REQUEST);
	}
}
