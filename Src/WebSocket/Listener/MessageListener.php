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
use W7\Core\Config\Event;
use W7\Core\Listener\ListenerAbstract;
use W7\Http\Server\Dispather;
use W7\WebSocket\Message\Frame;
use W7\WebSocket\Message\Request;
use W7\WebSocket\Message\Response;

class MessageListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $frame) = $params;
		$this->onMessage($server, $frame);
	}

	private function onMessage(Server $server, SwooleFrame $frame): void {
		ievent(Event::ON_USER_BEFORE_REQUEST);

		$context = App::getApp()->getContext();
		$context->setContextDataByKey('workid', $server->worker_id);
		$context->setContextDataByKey('coid', Coroutine::getuid());

		$frame = new Frame($frame);
		$psr7Request = Request::loadFromWebSocketFrame($frame);
		$psr7Response = Response::loadFromWebSocketFrame($frame);

		$dispather = \iloader()->singleton(Dispather::class);
		$psr7Response = $dispather->dispatch($psr7Request, $psr7Response);
		$psr7Response->send();

		ievent(Event::ON_USER_AFTER_REQUEST);
	}
}
