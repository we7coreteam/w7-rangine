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

namespace W7\WebSocket\Server;

use Swoole\WebSocket\Server as WebSocketServer;
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\SwooleEvent;

class Server extends ServerAbstract {
	public function getType() {
		return ServerEnum::TYPE_WEBSOCKET;
	}

	public function start() {
		if ($this->setting['dispatch_mode'] == 1 || $this->setting['dispatch_mode'] == 3) {
			throw new \RuntimeException('not support the dispatch mode, please reset config/server.php/common/dispatch_mode');
		}
		$this->server = $this->getServer();
		$this->setting['open_websocket_close_frame'] = false;
		$this->server->set($this->setting);

		//执行一些公共操作，注册事件等
		$this->registerService();

		ievent(SwooleEvent::ON_USER_BEFORE_START, [$this->server]);

		$this->server->start();
	}

	/**
	 * @var \Swoole\Server $server
	 * 通过侦听端口的方法创建服务
	 */
	public function listener($server) {
		throw new \RuntimeException('websocket server not support create by listener');
	}

	public function getServer() {
		if (empty($this->server)) {
			$this->server = new WebSocketServer($this->connection['host'], $this->connection['port'], $this->connection['mode'], $this->connection['sock_type']);
		}
		return $this->server;
	}
}
