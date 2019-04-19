<?php

namespace W7\Tcp\Protocol;

use Swoole\Server;

class Dispatcher
{
	public static function dispatch($protocol, Server $server, $fd, $data) {
		$dispatcher = self::getDispatcher($protocol);
		$dispatcher->dispatch($server, $fd, $data);
	}

	private static function getDispatcher($protocol) : IDispatcher {
		$class = '';
		switch ($protocol) {
			case 'json':
				$class = '\\W7\\Tcp\\Protocol\\Json\\Dispatcher';
				break;
			case 'grpc':
				$class = '\\W7\\Tcp\\Protocol\\Grpc\\Dispatcher';
				break;
			case 'thrift':
			default:
				$class = '\\W7\\Tcp\\Protocol\\Thrift\\Dispatcher';
		}

		return \iloader()->singleton($class);
	}
}