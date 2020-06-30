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

namespace W7\WebSocket\Listener;

use Swoole\Coroutine;
use Swoole\Websocket\Frame as SwooleFrame;
use Swoole\Websocket\Server;
use W7\Core\Facades\Container;
use W7\Core\Facades\Context;
use W7\Core\Facades\Event;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Core\Listener\ListenerAbstract;
use W7\Http\Message\Outputer\WebSocketResponseOutputer;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;
use W7\WebSocket\Server\Dispatcher;

class MessageListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $frame) = $params;
		$this->onMessage($server, $frame);
	}

	private function onMessage(Server $server, SwooleFrame $frame): bool {
		Context::setContextDataByKey('workid', $server->worker_id);
		Context::setContextDataByKey('coid', Coroutine::getuid());

		$collector = Container::get('ws-client')[$frame->fd] ?? [];

		/**
		 * @var Psr7Request $psr7Request
		 */
		$psr7Request = $collector[0];
		$psr7Request = $psr7Request->loadFromWSFrame($frame);

		/**
		 * @var Psr7Response $psr7Response
		 */
		$psr7Response = $collector[1];
		//握手的Response只是为了响应握手，只处才是真正返回数据的Response
		$psr7Response->setOutputer(new WebSocketResponseOutputer($server, $frame->fd));

		Context::setResponse($psr7Response);
		Context::setRequest($psr7Request);

		Event::dispatch(ServerEvent::ON_USER_BEFORE_REQUEST, [$psr7Request, $psr7Response, ServerEnum::TYPE_WEBSOCKET]);

		$dispatcher = Container::singleton(Dispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);

		Event::dispatch(ServerEvent::ON_USER_AFTER_REQUEST, [$psr7Request, $psr7Response, ServerEnum::TYPE_WEBSOCKET]);

		$psr7Response->send();

		Context::destroy();
	}
}
