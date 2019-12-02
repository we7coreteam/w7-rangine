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

use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\SwooleEvent;
use W7\Tcp\Collector\CollectorManager;

class CloseListener extends ListenerAbstract {
	public function run(...$params) {
		$server = $params[0];
		$fd = $params[1];
		$reactorId = $params[2];

		//删除数据绑定记录
		iloader()->get(CollectorManager::class)->del($fd);

		ievent(SwooleEvent::ON_USER_AFTER_CLOSE, [$server, $fd, $reactorId]);
	}
}
