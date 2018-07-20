<?php
/**
 * @author donknap
 * @date 18-7-20 上午9:14
 */

namespace W7\Http\Server;

use W7\Core\Base\ServerAbstract;

class Server extends ServerAbstract {
	public $type = parent::TYPE_HTTP;

	public function start() {
		if (!empty($this->setting['open_http2_protocol'])) {
			$serverType = SWOOLE_SOCK_TCP|SWOOLE_SSL;
		}

		$this->server = new Server($this->setting['host'], $this->httpSetting['port'], $this->httpSetting['mode'], $this->httpSetting['type']);
	}

	public function stop() {

	}

	public function reload() {

	}

	public function isRun() {
		return false;
	}

	public function getServer() {

	}
}