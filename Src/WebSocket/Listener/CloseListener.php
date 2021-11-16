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

use Swoole\Server;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\WebSocket\Collector\FdCollector;

class CloseListener extends ListenerAbstract {
	/**
	 * @throws \Exception
	 */
	public function run(...$params) {
		[$server, $fd, $reactorId] = $params;
		$this->onClose($server, $fd, $reactorId);
	}

	/**
	 * @throws \Exception
	 */
	private function onClose(Server $server, int $fd, int $reactorId): void {
		$fdCollector = FdCollector::instance();
		$collector =  $fdCollector->get($fd, []);
		if ($collector) {
			/**
			 * @var Psr7Request $psr7Request
			 */
			$psr7Request = $collector[0];
			$psr7Request->session->close();
		}

		$fdCollector->delete($fd);

		$this->getEventDispatcher()->dispatch(ServerEvent::ON_USER_AFTER_CLOSE, [$server, $fd, $reactorId, ServerEnum::TYPE_WEBSOCKET]);
	}
}
