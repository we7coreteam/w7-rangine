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
use W7\Core\Server\SwooleEvent;

class CloseListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $fd, $reactorId) = $params;
		$this->onClose($server, $fd, $reactorId);
	}

	private function onClose(Server $server, int $fd, int $reactorId): void {
		//删除数据绑定记录
		ievent(SwooleEvent::ON_USER_BEFORE_CLOSE, [$server, $fd, $reactorId]);
	}
}
