<?php
/**
 * @author donknap
 * @date 18-7-20 上午9:14
 */

namespace W7\Http\Server;

use Swoole\Http\Server as HttpServer;
use W7\Core\Dispatcher\EventDispatcher;
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\SwooleEvent;

class Server extends ServerAbstract {
	public function getType() {
		return parent::TYPE_HTTP;
	}

	public function start() {
		if (!empty($this->setting['open_http2_protocol'])) {
			$this->connection['type'] = SWOOLE_SOCK_TCP|SWOOLE_SSL;
		}
		$this->server = new HttpServer($this->connection['host'], $this->connection['port'], $this->connection['mode'], $this->connection['sock_type']);
		$this->server->set($this->setting);

		/**
		 * 该版本暂时放在此, 流程冲突, 在容器分支中移除
		 */
		iloader()->singleton(EventDispatcher::class)->registerSwooleUserEvent();
		ievent(SwooleEvent::ON_USER_BEFORE_START, [$this->server]);
		//执行一些公共操作，注册事件等
		$this->registerService();

		$this->server->start();
	}
}
