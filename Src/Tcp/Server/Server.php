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
use W7\Core\View\Provider\ViewProvider;
use W7\Tcp\Provider\ServiceProvider;
use W7\Tcp\Session\Provider\SessionProvider;

class Server extends SwooleServerAbstract {
	protected $providerMap = [
		ServiceProvider::class,
		SessionProvider::class,
		ViewProvider::class
	];

	public function getType() {
		return ServerEnum::TYPE_TCP;
	}

	protected function checkSetting() {
		parent::checkSetting();
		if (in_array($this->setting['dispatch_mode'], [1, 3, 7])) {
			throw new \RuntimeException("dispatch mode can't be 1,3,7, please reset config/server.php/common/dispatch_mode");
		}
	}

	public function start() {
		$this->server = $this->getServer();
		$this->server->set($this->setting);

		//执行一些公共操作，注册事件等
		$this->registerService();

		ievent(ServerEvent::ON_USER_BEFORE_START, [$this->server]);

		$this->server->start();
	}

	public function getServer() {
		if (empty($this->server)) {
			$this->server = new TcpServer($this->setting['host'], $this->setting['port'], $this->setting['mode'], $this->setting['sock_type']);
		}
		return $this->server;
	}

	/**
	 * @var \Swoole\Server $server
	 * 通过侦听端口的方法创建服务
	 */
	public function listener(\Swoole\Server $server) {
		$server = $server->addListener($this->setting['host'], $this->setting['port'], $this->setting['sock_type']);
		//tcp需要强制关闭其它协议支持，否则继续父服务
		$server->set([
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
