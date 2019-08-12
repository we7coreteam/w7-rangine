<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:18
 */

namespace W7\Core\Listener;

use W7\Core\Log\LogManager;

class WorkerStopListener implements ListenerInterface {
	public function run(...$params) {
		iloader()->singleton(LogManager::class)->flushLog();
	}
}
