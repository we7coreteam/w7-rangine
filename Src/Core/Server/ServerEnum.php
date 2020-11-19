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

use W7\Fpm\Server\Server as FpmServer;
use W7\Http\Server\Server as HttpServer;
use W7\Process\Server\Server as ProcessServer;
use W7\Reload\Server\Server as ReloadServer;
use W7\Tcp\Server\Server as TcpServer;
use W7\WebSocket\Server\Server as WebSocketServer;

class ServerEnum {
	const TYPE_FPM = 'fpm';
	const TYPE_HTTP = 'http';
	const TYPE_RPC = 'rpc';
	const TYPE_TCP = 'tcp';
	const TYPE_WEBSOCKET = 'webSocket';
	//处理用户输入的小写websocket找不到server的问题
	const TYPE_LOWER_WEBSOCKET = 'websocket';
	const TYPE_PROCESS = 'process';
	const TYPE_RELOAD = 'reload';

	public static $ALL_SERVER = [
		self::TYPE_WEBSOCKET => WebSocketServer::class,
		self::TYPE_LOWER_WEBSOCKET => WebSocketServer::class,
		self::TYPE_HTTP => HttpServer::class,
		self::TYPE_FPM => FpmServer::class,
		self::TYPE_TCP => TcpServer::class,
		self::TYPE_PROCESS => ProcessServer::class,
		self::TYPE_RELOAD => ReloadServer::class
	];

	const MODE_LIST = [
		//SWOOLE_BASE
		1 => 'base',
		//SWOOLE_PROCESS
		2 => 'process',
	];

	const SOCK_LIST = [
		//SWOOLE_TCP
		1 => 'tcp',
		//SWOOLE_UDP
		2 => 'udp',
		//SWOOLE_TCP6
		3 => 'tcp6',
		//SWOOLE_UDP6
		4 => 'udp6',
		//SWOOLE_UNIX_STREAM
		5 => 'unix_stream',
		//SWOOLE_UNIX_DGRAM
		6 => 'unix_dgram'
	];

	public static function registerServer($type, string $class) {
		static::$ALL_SERVER[$type] = $class;
	}
}
