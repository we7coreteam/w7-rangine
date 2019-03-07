<?php
/**
 * @author donknap
 * @date 19-3-4 下午6:09
 */

namespace W7\Tcp\Listener;


use Swoole\Server;
use W7\Core\Listener\ListenerAbstract;

class ReceiveListener extends ListenerAbstract {
	public function run(...$params) {
		/**
		 * @var Server $server
		 */
		list($server, $fd, $reactorId, $data) = $params;
		print_r($params);
	}
}