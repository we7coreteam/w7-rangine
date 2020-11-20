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

namespace W7\Tcp\Listener;

use Swoole\Coroutine;
use Swoole\Server;
use W7\Core\Facades\Container;
use W7\Core\Facades\Context;
use W7\Core\Facades\Event;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;
use W7\Tcp\Server\Dispatcher as RequestDispatcher;

class ReceiveListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $fd, $reactorId, $data) = $params;

		$this->dispatch($server, $reactorId, $fd, $data);
	}

	private function dispatch(Server $server, $reactorId, $fd, $data) {
		Context::setContextDataByKey('fd', $fd);
		Context::setContextDataByKey('reactorid', $reactorId);
		Context::setContextDataByKey('workid', $server->worker_id);
		Context::setContextDataByKey('coid', Coroutine::getuid());

		$collector = Container::get('tcp-client')[$fd] ?? [];

		/**
		 * @var Psr7Request $psr7Request
		 */
		$psr7Request = $collector[0];
		$psr7Request = $psr7Request->loadFromTcpData($data);

		/**
		 * @var Psr7Response $psr7Response
		 */
		$psr7Response = $collector[1];

		Context::setResponse($psr7Response);
		Context::setRequest($psr7Request);

		Event::dispatch(ServerEvent::ON_USER_BEFORE_REQUEST, [$psr7Request, $psr7Response, ServerEnum::TYPE_TCP]);

		/**
		 * @var RequestDispatcher $dispatcher
		 */
		$dispatcher = Container::singleton(RequestDispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);

		Event::dispatch(ServerEvent::ON_USER_AFTER_REQUEST, [$psr7Request, $psr7Response, ServerEnum::TYPE_TCP]);

		$psr7Response->send();
	}
}
