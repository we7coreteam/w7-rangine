<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:18
 */

namespace W7\Core\Listener;

use W7\App;

class WorkerStartListener implements ListenerInterface {
	public function run(...$params) {
		if (ini_get('opcache.enable') || ini_get('opcache.enable_cli')) {
			opcache_reset();
		}

		\isetProcessTitle( 'w7swoole ' . App::$server->type . (App::$server->server->taskworker ? ' task' : '')  . ' worker process');
	}
}
