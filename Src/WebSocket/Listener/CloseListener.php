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
use W7\Core\Server\ServerEvent;
use W7\Http\Message\Server\Request as Psr7Request;

class CloseListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $fd, $reactorId) = $params;
		$this->onClose($server, $fd, $reactorId);
	}

	private function onClose(Server $server, int $fd, int $reactorId): void {
		if (icontainer()->has('ws-client')) {
			$collector = icontainer()->get('ws-client')[$fd] ?? [];
			if ($collector) {
				/**
				 * @var Psr7Request $psr7Request
				 */
				$psr7Request = $collector[0];
				$psr7Request->session->close();
			}
		}

		//删除数据绑定记录
		icontainer()->append('ws-client', [
			$fd => []
		], []);

		ievent(ServerEvent::ON_USER_AFTER_CLOSE, [$server, $fd, $reactorId]);
	}
}
