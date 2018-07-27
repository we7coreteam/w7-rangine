<?php
/**
 * @author donknap
 * @date 18-7-20 上午9:14
 */

namespace W7\Http\Server;

use W7\Core\Base\ServerAbstract;
use W7\Core\Base\SwooleHttpServer;
use W7\Core\Helper\Context;


class Server extends ServerAbstract {

	public $type = parent::TYPE_HTTP;

	public function start() {
		if (!empty($this->setting['open_http2_protocol'])) {
			$this->connection['type'] = SWOOLE_SOCK_TCP|SWOOLE_SSL;
		}
		//Dispather::addRoute();
		$this->server = new SwooleHttpServer($this->connection['host'], $this->connection['port'], $this->connection['mode'], $this->connection['sock_type']);
		$this->server->set($this->setting);
		Context::setContextDataByKey('test', '1123');
		$this->registerEventListener();
		$this->registerProcesser();

		$this->server->context = Context::getContextData();

		$this->server->start();
	}
}