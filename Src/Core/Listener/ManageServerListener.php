<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:08
 */

namespace W7\Core\Listener;

use Swoole\Http\Server;

trait ManageServerListener {
	public function onStart(Server $server) {
		\ioutputer()->writeln('server start');
	}

	public function onManagerStart(Server $server) {
		\ioutputer()->writeln('manager start');
	}

	public function onWorkerStart(Server $server, $workerId) {
		\ioutputer()->writeln('work start' . $workerId);
	}

	public function onPipeMessage(Server $server, $srcWorkerId, $message) {

	}
}