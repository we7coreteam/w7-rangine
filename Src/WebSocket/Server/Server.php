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
use W7\Core\Facades\Event;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Core\Server\SwooleServerAbstract;
use W7\Core\View\Provider\ViewProvider;
use W7\WebSocket\Session\Provider\SessionProvider;

class Server extends SwooleServerAbstract {
	protected $providerMap = [
		'websocket-session' => SessionProvider::class,
		'websocket-view' => ViewProvider::class
	];

	public function getType() {
		return ServerEnum::TYPE_WEBSOCKET;
	}

	protected function checkSetting() {
		parent::checkSetting();
		if (in_array($this->setting['dispatch_mode'], [1, 3])) {
			throw new \RuntimeException("dispatch mode can't be 1,3, please reset config/server.php/common/dispatch_mode");
		}
	}

	protected function getDefaultSetting(): array {
		$setting = parent::getDefaultSetting();
		$setting['dispatch_mode'] = 2;

		return $setting;
	}

	public function getServer() {
		if (empty($this->server)) {
			$this->server = new WebSocketServer($this->setting['host'], $this->setting['port'], $this->setting['mode'], $this->setting['sock_type']);
		}
		return $this->server;
	}

	public function start() {
		$this->server = $this->getServer();
		$this->setting['open_websocket_close_frame'] = false;
		$this->server->set($this->setting);

		//执行一些公共操作，注册事件等
		$this->registerService();

		Event::dispatch(ServerEvent::ON_USER_BEFORE_START, [$this->server, $this->getType()]);

		$this->server->start();
	}

	/**
	 * @var \Swoole\Server $server
	 * 通过侦听端口的方法创建服务
	 */
	public function listener(\Swoole\Server $server) {
		throw new \RuntimeException('websocket server not support create by listener');
	}
}
