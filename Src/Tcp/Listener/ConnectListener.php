<?php
/**
 * @author donknap
 * @date 19-3-4 下午6:15
 */

namespace W7\Tcp\Listener;

use W7\Core\Listener\ListenerAbstract;
use W7\Http\Message\Server\Request;
use W7\WebSocket\Collector\CollectorManager;

class ConnectListener extends ListenerAbstract {
	public function run(...$params) {
		iloader()->get(CollectorManager::class)->set($params[1], new Request('POST', '/'));
	}
}