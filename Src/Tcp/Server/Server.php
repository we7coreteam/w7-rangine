<?php
/**
 * @author donknap
 * @date 18-11-6 下午2:10
 */

namespace W7\Tcp\Server;

use Swoole\Server as TcpServer;
use W7\Core\Config\Event;
use W7\Core\Server\ServerAbstract;

class Server extends ServerAbstract {
	public function getType() {
		return parent::TYPE_TCP;
	}

	public function start() {
		$this->server = new TcpServer($this->connection['host'], $this->connection['port'], $this->connection['mode'], $this->connection['sock_type']);
		$this->server->set($this->setting);

		ievent(Event::ON_USER_BEFORE_START, [$this->server]);
		//执行一些公共操作，注册事件等
		$this->registerService();

		$this->server->start();
	}
}