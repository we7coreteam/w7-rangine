<?php

namespace W7\Tcp\Protocol;

use Swoole\Server;
use W7\Tcp\Protocol\Thrift\Dispatcher as DispatcherThrift;

class Dispatcher {
	private static $protocolMap = [
		'thrift' => DispatcherThrift::class
	];

	/**解析data数据，并转到对应的控制器下
	 * @param $protocol
	 * @param Server $server
	 * @param $fd
	 * @param $data
	 */
	public static function dispatch($protocol, Server $server, $fd, $data) {
		$dispatcher = self::getDispatcher($protocol);
		$dispatcher->dispatch($server, $fd, $data);
	}

	private static function getDispatcher($protocol) : DispatcherInterface {
		if (empty(self::$protocolMap[$protocol])) {
			$protocol = 'thrift';
		}
		return \iloader()->singleton(self::$protocolMap[$protocol]);
	}
}