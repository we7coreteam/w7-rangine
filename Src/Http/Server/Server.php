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

namespace W7\Http\Server;

use Swoole\Http\Server as HttpServer;
use W7\WebSocket\Server\Server as WebSocketServer;
use W7\App;
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\SwooleEvent;
use W7\Core\Server\ServerEnum;

class Server extends ServerAbstract {
	public function getType() {
		return ServerEnum::TYPE_HTTP;
	}

	public function start() {
		if (!empty($this->setting['open_http2_protocol'])) {
			$this->setting['type'] = SWOOLE_SOCK_TCP|SWOOLE_SSL;
		}
		$this->server = $this->getServer();
		$this->setting['http_parse_post'] = true;
		$this->server->set($this->setting);

		//执行一些公共操作，注册事件等
		$this->registerService();

		ievent(SwooleEvent::ON_USER_BEFORE_START, [$this->server]);

		$this->server->start();
	}

	public function getServer() {
		if (empty($this->server)) {
			$this->server = new HttpServer($this->setting['host'], $this->setting['port'], $this->setting['mode'], $this->setting['sock_type']);
		}
		return $this->server;
	}

	/**
	 * @var \Swoole\Server $server
	 * 通过侦听端口的方法创建服务
	 */
	public function listener(\Swoole\Server $server) {
		if (App::$server instanceof WebSocketServer) {
			(new SwooleEvent())->websocketSupportHttp();
		}
	}
}
