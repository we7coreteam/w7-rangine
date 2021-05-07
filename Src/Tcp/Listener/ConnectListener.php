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
use W7\Contract\Session\SessionInterface;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Http\Message\Outputer\TcpResponseOutputer;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;

class ConnectListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $fd, $reactorId) = $params;
		return $this->onConnect($server, $fd, $reactorId);
	}

	private function onConnect(Server $server, $fd, $reactorId) {
		/**
		 * @var Psr7Request $psr7Request
		 */
		$psr7Request = new Psr7Request('', '');
		$psr7Response = new Psr7Response();
		$psr7Response->setOutputer(new TcpResponseOutputer($server, $fd));

		//TCP session guarantees that data is shared in this connection, and Response cannot delegate SessionID, so there is no data shared between two connections
		$psr7Request->session = $this->getContainer()->clone(SessionInterface::class);
		$psr7Request->session->start($psr7Request);

		$this->getContainer()->append('tcp-client', [
			$fd => [$psr7Request, $psr7Response]
		], []);

		$this->getEventDispatcher()->dispatch(ServerEvent::ON_USER_AFTER_OPEN, [$server, $fd, $psr7Request, ServerEnum::TYPE_TCP]);
	}
}
