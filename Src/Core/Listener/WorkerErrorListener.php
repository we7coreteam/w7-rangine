<?php
/**
 * @author donknap
 * @date 18-11-5 下午3:49
 */

namespace W7\Core\Listener;


use Swoole\Http\Server;

class WorkerErrorListener implements ListenerInterface {
	public function run(Server $server, $workId, $workPid, $exitCode, $signal) {
		if ($exitCode == '255') {

		}
	}
}