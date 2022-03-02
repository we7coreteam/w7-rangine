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

use Swoole\Websocket\Frame as SwooleFrame;
use Swoole\Websocket\Server;
use W7\Core\Listener\ListenerAbstract;
use W7\Http\Message\Outputer\WebSocketResponseOutputer;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;
use W7\WebSocket\Server\Dispatcher;

class MessageListener extends ListenerAbstract {
	public function run(...$params) {
		[$server, $frame] = $params;
		$this->onMessage($server, $frame);
	}

	//待优化 session
	protected function getRequestAndResponse(Server $server, SwooleFrame $frame) {
		$psr7Request = new Psr7Request('POST', '/');
		$psr7Request = $psr7Request->withAttribute('fd', $frame->fd);

		$psr7Response = new Psr7Response();
		$psr7Response->setOutputer(new WebSocketResponseOutputer($server, $frame->fd));

		return [$psr7Request, $psr7Response];
	}

	private function onMessage(Server $server, SwooleFrame $frame) {
		$this->getContext()->setContextDataByKey('workid', $server->worker_id);
		$this->getContext()->setContextDataByKey('coid', $this->getContext()->getCoroutineId());

		/**
		 * @var Psr7Request $psr7Request
		 */
		/**
		 * @var Psr7Response $psr7Response
		 */
		[$psr7Request, $psr7Response] = $this->getRequestAndResponse($server, $frame);
		$psr7Request = $psr7Request->withAttribute('frame', $frame);
		$psr7Request = $psr7Request->loadFromWSFrame($frame);

		$dispatcher = $this->getContainer()->get(Dispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);

		$psr7Response->send();
	}
}
