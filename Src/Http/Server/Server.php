<?php
/**
 * @author donknap
 * @date 18-7-20 上午9:14
 */

namespace W7\Http\Server;

use W7\Core\Server\ServerAbstract;
use W7\Core\Config\Event;

class Server extends ServerAbstract {
	public $type = parent::TYPE_HTTP;

	public function start() {
		if (!empty($this->setting['open_http2_protocol'])) {
			$this->connection['type'] = SWOOLE_SOCK_TCP|SWOOLE_SSL;
		}
		$this->server = new \swoole_http_server($this->connection['host'], $this->connection['port'], $this->connection['mode'], $this->connection['sock_type']);
		$this->server->set($this->setting);

		ievent(Event::ON_USER_BEFORE_START);
		//执行一些公共操作，注册事件等
		$this->registerService();

		$this->server->start();
	}
}
