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

namespace W7\Tcp\Protocol\Json;

use Swoole\Server;
use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;
use W7\Tcp\Protocol\DispatcherInterface;
use W7\Tcp\Server\Dispatcher as RequestDispatcher;

class Dispatcher implements DispatcherInterface {
	public function dispatch(Server $server, $fd, $data) {
		$params = json_decode($data, true);
		$params['url'] = $params['url'] ?? '';
		$params['data'] = $params['data'] ?? [];

		$psr7Request = new Request('POST', $params['url'], [], null);
		$psr7Request = $psr7Request->withParsedBody($params['data']);
		$psr7Response = new Response();

		/**
		 * @var RequestDispatcher $dispatcher
		 */
		$dispatcher = \iloader()->singleton(RequestDispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);

		$content = $psr7Response->getBody()->getContents();
		$server->send($fd, $content);
	}
}
