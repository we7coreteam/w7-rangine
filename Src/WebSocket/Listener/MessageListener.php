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
use W7\Core\Server\ServerEvent;
use W7\App;
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
		$context = App::getApp()->getContext();
		$context->setContextDataByKey('workid', $server->worker_id);
		$context->setContextDataByKey('coid', Coroutine::getuid());

		$collector = icontainer()->get('ws-client')[$frame->fd] ?? [];
		if (empty($collector)) {
			$server->push($frame->fd, 'Invalid Request or Response, please reconnect');
			$server->disconnect();
			return false;
		}

		/**
		 * @var Psr7Request $psr7Request
		 */
		$psr7Request = $collector[0];
		$psr7Request = $psr7Request->loadFromWSFrame($frame);

		echo $frame->fd;
		echo PHP_EOL;
		echo PHP_EOL;
		/**
		 * @var Psr7Response $psr7Response
		 */
		$psr7Response = $collector[1];
		//握手的Response只是为了响应握手，只处才是真正返回数据的Response
		$psr7Response->setOutputer(new WebSocketResponseOutputer($server, $frame->fd));

		App::getApp()->getContext()->setResponse($psr7Response);
		App::getApp()->getContext()->setRequest($psr7Request);

		ievent(ServerEvent::ON_USER_BEFORE_REQUEST, [$psr7Request, $psr7Response]);

		$dispatcher = icontainer()->singleton(Dispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);
		$psr7Response->send();

		ievent(ServerEvent::ON_USER_AFTER_REQUEST);
		icontext()->destroy();
		return true;
	}
}
