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

namespace W7\Core\Server;

use W7\Crontab\Server\Server as CrontabServer;
use W7\Http\Server\Server as HttpServer;
use W7\Process\Server\Server as ProcessServer;
use W7\Reload\Server\Server as ReloadServer;
use W7\Tcp\Server\Server as TcpServer;
use W7\WebSocket\Server\Server as WebSocketServer;

class ServerEnum {
	const TYPE_HTTP = 'http';
	const TYPE_RPC = 'rpc';
	const TYPE_TCP = 'tcp';
	const TYPE_WEBSOCKET = 'webSocket';
	const TYPE_PROCESS = 'process';
	const TYPE_CRONTAB = 'crontab';
	const TYPE_RELOAD = 'reload';

	const ALL_SERVER = [
		self::TYPE_WEBSOCKET => WebSocketServer::class,
		self::TYPE_HTTP => HttpServer::class,
		self::TYPE_TCP => TcpServer::class,
		self::TYPE_PROCESS => ProcessServer::class,
		self::TYPE_CRONTAB => CrontabServer::class,
		self::TYPE_RELOAD => ReloadServer::class
	];

	const MODE_LIST = [
		SWOOLE_BASE => 'base',
		SWOOLE_PROCESS => 'process',
	];

	const SOCK_LIST = [
		SWOOLE_SOCK_TCP => 'tcp',
		SWOOLE_SOCK_TCP6 => 'tcp6',
		SWOOLE_SOCK_UDP => 'udp',
		SWOOLE_SOCK_UDP6 => 'udp6',
		SWOOLE_SOCK_UNIX_DGRAM => 'dgram',
		SWOOLE_SOCK_UNIX_STREAM => 'stream'
	];
}
