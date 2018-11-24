<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:18
 */

namespace W7\Core\Listener;

use Swoole\Server;
use W7\App;
use W7\Core\Process\CrontabProcess;

class StartListener implements ListenerInterface {
	public function run(...$params) {
		\isetProcessTitle( 'w7swoole ' . App::$server->type . ' master process');

		while (true) {
			$process = iprocess(CrontabProcess::class);
			$process->write('hello world');
			sleep(1);
		}
	}
}
