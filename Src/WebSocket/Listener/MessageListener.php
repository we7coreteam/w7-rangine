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

use Illuminate\Contracts\Container\BindingResolutionException;
use Swoole\Websocket\Frame as SwooleFrame;
use Swoole\Websocket\Server;
use W7\Core\Listener\ListenerAbstract;
use W7\Http\Message\Outputer\WebSocketResponseOutputer;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;
use W7\WebSocket\Collector\FdCollector;
use W7\WebSocket\Server\Dispatcher;

class MessageListener extends ListenerAbstract {
	/**
	 * @throws \ReflectionException
	 * @throws BindingResolutionException
	 */
	public function run(...$params) {
		[$server, $frame] = $params;
		$this->onMessage($server, $frame);
	}

	/**
	 * @throws \ReflectionException
	 * @throws BindingResolutionException
	 */
	private function onMessage(Server $server, SwooleFrame $frame): void {
		$this->getContext()->setContextDataByKey('workid', $server->worker_id);
		$this->getContext()->setContextDataByKey('coid', $this->getContext()->getCoroutineId());

		$collector = FdCollector::instance()->get($frame->fd, []);

		/**
		 * @var Psr7Request $psr7Request
		 */
		$psr7Request = $collector[0];
		$psr7Request = $psr7Request->loadFromWSFrame($frame);
		/**
		 * @var Psr7Response $psr7Response
		 */
		$psr7Response = $collector[1];
		$psr7Response->setOutputer(new WebSocketResponseOutputer($server, $frame->fd));

		$dispatcher = $this->getContainer()->get(Dispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);

		$psr7Response->send();

	}
}
