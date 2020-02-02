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
use W7\App;
use W7\Core\Listener\ListenerAbstract;
use W7\WebSocket\Message\Frame;
use W7\WebSocket\Message\Request;
use W7\WebSocket\Message\Response;
use W7\WebSocket\Server\Dispatcher;

class MessageListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $frame) = $params;
		$this->onMessage($server, $frame);
	}

	private function onMessage(Server $server, SwooleFrame $frame): void {
		$context = App::getApp()->getContext();
		$context->setContextDataByKey('workid', $server->worker_id);
		$context->setContextDataByKey('coid', Coroutine::getuid());

		$frame = new Frame($frame);
		$psr7Request = Request::loadFromWebSocketFrame($frame);
		$psr7Response = Response::loadFromWebSocketFrame($frame);

		// ievent(SwooleEvent::ON_USER_BEFORE_REQUEST, [$psr7Request, $psr7Response]);

		$dispatcher = \iloader()->get(Dispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);
		$psr7Response->send();

		// ievent(SwooleEvent::ON_USER_AFTER_REQUEST);
	}
}
