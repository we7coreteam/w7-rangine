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

namespace W7\Tcp\Protocol\Thrift\Core;

use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;
use W7\Tcp\Server\Dispatcher;

class DispatcherHandle implements DispatcherIf {
	public function run($params) {
		$params = json_decode($params, true);
		$params['url'] = $params['url'] ?? '';
		$params['data'] = $params['data'] ?? [];

		$psr7Request = new Request('POST', $params['url'], [], null);
		$psr7Request = $psr7Request->withParsedBody($params['data']);
		$psr7Response = new Response();

		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = \iloader()->get(Dispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);

		return $psr7Response->getBody()->getContents();
	}
}
