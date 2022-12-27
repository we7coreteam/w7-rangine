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

use Swoole\Server;
use W7\Core\Listener\ListenerAbstract;
use W7\Http\Message\Outputer\TcpResponseOutputer;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;
use W7\Tcp\Server\Dispatcher as RequestDispatcher;

class ReceiveListener extends ListenerAbstract {
	public function run(...$params) {
		[$server, $fd, $reactorId, $data] = $params;

		$this->dispatch($server, $reactorId, $fd, $data);
	}

	//待优化 session
	protected function getRequestAndResponse(Server $server, $fd) {
		$psr7Request = new Psr7Request('POST', '/');
		$psr7Request = $psr7Request->withAttribute('fd', $fd);

		$psr7Response = new Psr7Response();
		$psr7Response->setOutputer(new TcpResponseOutputer($server, $fd));

		return [$psr7Request, $psr7Response];
	}

	private function dispatch(Server $server, $reactorId, $fd, $data) {
		$this->getContext()->setContextDataByKey('reactorid', $reactorId);
		$this->getContext()->setContextDataByKey('workid', $server->worker_id);
		$this->getContext()->setContextDataByKey('coid', $this->getContext()->getCoroutineId());

		/**
		 * @var Psr7Request $psr7Request
		 */
		/**
		 * @var Psr7Response $psr7Response
		 */
		[$psr7Request, $psr7Response] = $this->getRequestAndResponse($server, $fd);
		$psr7Request = $psr7Request->loadFromTcpData($data);

		/**
		 * @var RequestDispatcher $dispatcher
		 */
		$dispatcher = $this->getContainer()->get(RequestDispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);

		$psr7Response->send();
	}
}
