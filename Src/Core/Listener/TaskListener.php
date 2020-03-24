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
use W7\Core\Dispatcher\TaskDispatcher;

class TaskListener implements ListenerInterface {
	public function run(...$params) {
		list($server, $task) = $params;

		return $this->dispatchTask($server, $task);
	}

	private function dispatchTask(Server $server, Task $task) {
		/**
		 * @var TaskDispatcher $taskDispatcher
		 */
		$taskDispatcher = icontainer()->get(TaskDispatcher::class);
		try {
			$result = $taskDispatcher->dispatch($server, $task->id, $task->worker_id, $task->data);
		} catch (\Throwable $exception) {
			$task->finish($exception->getMessage());
			return;
		}
		if (empty($result)) {
			$result = true;
		}
		$task->finish($result);
	}
}
