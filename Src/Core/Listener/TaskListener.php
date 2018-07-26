<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:18
 */

namespace W7\Core\Listener;

use Swoole\Http\Server;

class TaskListener {
	public function onFinish(Server $server, $taskId, $data) {

	}

	public function onTask(Server $server, $taskId, $workerId, $data) {

		return true;
	}
	public static function run()
    {

    }
}