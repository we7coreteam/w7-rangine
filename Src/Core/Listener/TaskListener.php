<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Listener;

use Swoole\Http\Server;
use Swoole\Server\Task;
use W7\Core\Facades\Task as TaskFacade;

class TaskListener implements ListenerInterface {
	public function run(...$params) {
		list($server, $task) = $params;

		return $this->dispatchTask($server, $task);
	}

	private function dispatchTask(Server $server, Task $task) {
		try {
			$result = TaskFacade::dispatchNow($task->data, $server, $task->id, $task->worker_id);
		} catch (\Throwable $exception) {
			$result = $exception->getMessage();
		}
		if (empty($result)) {
			$result = true;
		}
		$task->finish($result);
	}
}
