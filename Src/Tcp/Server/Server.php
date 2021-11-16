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

namespace W7\Tcp\Server;

use Swoole\Server as TcpServer;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Core\Server\SwooleServerAbstract;

class Server extends SwooleServerAbstract {
	public function getType() {
		return ServerEnum::TYPE_TCP;
	}

	protected function checkSetting(): void {
		parent::checkSetting();
		if (in_array($this->setting['dispatch_mode'], [1, 3, 7], true)) {
			throw new \RuntimeException("dispatch mode can't be 1,3,7, please reset config/server.php/common/dispatch_mode");
		}
	}

	/**
	 * @throws \Exception
	 */
	public function start(): void {
		$this->server = $this->getServer();
		$this->server->set($this->filterServerSetting());

		$this->registerService();

		$this->getEventDispatcher()->dispatch(ServerEvent::ON_USER_BEFORE_START, [$this->server, $this->getType()]);

		$this->server->start();
	}

	public function getServer() {
		if (empty($this->server)) {
			$this->server = new TcpServer($this->setting['host'], $this->setting['port'], $this->setting['mode'], $this->setting['sock_type']);
		}
		return $this->server;
	}

	public function listener(\Swoole\Server $server): void {
		$this->server = $server->addListener($this->setting['host'], $this->setting['port'], $this->setting['sock_type']);
		$this->server->set([
			'open_http2_protocol' => false,
			'open_http_protocol' => false,
			'open_websocket_protocol' => false,
		]);

		$this->registerService();
	}

	protected function getDefaultSetting() : array {
		$setting = parent::getDefaultSetting();
		$setting['dispatch_mode'] = 2;

		return $setting;
	}
}
