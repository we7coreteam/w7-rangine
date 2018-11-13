<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:18
 */

namespace W7\Core\Listener;

use Swoole\Http\Server;
use W7\App;

class WorkerStartListener implements ListenerInterface {
	public function run(...$params) {
		\isetProcessTitle( 'w7swoole ' . App::$server->type . ' worker process');
	}
}
