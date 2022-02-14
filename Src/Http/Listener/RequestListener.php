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

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use W7\Core\Listener\ListenerAbstract;
use W7\Http\Message\Outputer\SwooleResponseOutputer;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;
use W7\Http\Server\Dispatcher;

class RequestListener extends ListenerAbstract {
	public function run(...$params) {
		[$server, $request, $response] = $params;
		$this->dispatch($server, $request, $response);
	}

	private function dispatch(Server $server, Request $request, Response $response) {
		$this->getContext()->setContextDataByKey('workid', $server->worker_id);
		$this->getContext()->setContextDataByKey('coid', $this->getContext()->getCoroutineId());

		$psr7Request = Psr7Request::loadFromSwooleRequest($request);
		$psr7Response = new Psr7Response();
		$psr7Response->setOutputer(new SwooleResponseOutputer($response));

		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = $this->getContainer()->get(Dispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);

		$psr7Response->send();
	}
}
