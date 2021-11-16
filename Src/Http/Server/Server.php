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
use W7\Core\Server\SwooleServerAbstract;
use W7\WebSocket\Server\Server as WebSocketServer;
use W7\App;
use W7\Core\Server\ServerEvent;
use W7\Core\Server\ServerEnum;

class Server extends SwooleServerAbstract {
	public function getType() {
		return ServerEnum::TYPE_HTTP;
	}

	public function getServer() {
		if (empty($this->server)) {
			$this->server = new HttpServer($this->setting['host'], $this->setting['port'], $this->setting['mode'], $this->setting['sock_type']);
		}
		return $this->server;
	}

	/**
	 * @throws \Exception
	 */
	public function start(): void {
		if (!empty($this->setting['open_http2_protocol'])) {
			$this->setting['type'] = SWOOLE_SOCK_TCP|SWOOLE_SSL;
		}
		$this->server = $this->getServer();
		$this->setting['http_parse_post'] = true;
		$this->server->set($this->filterServerSetting());

		$this->registerService();

		$this->getEventDispatcher()->dispatch(ServerEvent::ON_USER_BEFORE_START, [$this->server, $this->getType()]);

		$this->server->start();
	}

	public function listener(\Swoole\Server $server): void {
		if (App::$server instanceof WebSocketServer) {
			if ($server->port !== $this->setting['port']) {
				$this->server = $server->addListener($this->setting['host'], $this->setting['port'], $this->setting['sock_type']);
				//TCP needs to force other protocol support to be turned off, or the parent service will continue
				$this->server->set([
					'open_http2_protocol' => false,
					'open_http_protocol' => true,
					'open_websocket_protocol' => false
				]);
			} else {
				$this->server = $server;
			}

			$this->registerService();
		}
	}
}
