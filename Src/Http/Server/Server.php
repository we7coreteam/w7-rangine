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
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\SwooleEvent;

class Server extends ServerAbstract {
	public $type = parent::TYPE_HTTP;

	public function start() {
		if (!empty($this->setting['open_http2_protocol'])) {
			$this->connection['type'] = SWOOLE_SOCK_TCP|SWOOLE_SSL;
		}
		$this->server = $this->getServer();
		$this->server->set($this->setting);

		/**
		 * 该版本暂时放在此, 流程冲突, 在容器分支中移除
		 */
		iloader()->singleton(SwooleEvent::class)->register();

		ievent(SwooleEvent::ON_USER_BEFORE_START, [$this->server]);
		//执行一些公共操作，注册事件等
		$this->registerService();

		$this->server->start();
	}

	public function getServer() {
		if (empty($this->server)) {
			$this->server = new HttpServer($this->connection['host'], $this->connection['port'], $this->connection['mode'], $this->connection['sock_type']);
		}
		return $this->server;
	}
}
