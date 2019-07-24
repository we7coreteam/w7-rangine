<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:18
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

	private function dispatchTask(Server $server,  Task $task) {
		/**
		 * @var TaskDispatcher $taskDispatcher
		 */
		$taskDispatcher = iloader()->get(TaskDispatcher::class);
		try {
			$result = $taskDispatcher->dispatch($server, $task->id, $task->worker_id, $task->data);
		} catch (\Exception $exception) {
			$task->finish($exception->getMessage());
			return;
		}
		if (empty($result)) {
			$result = true;
		}
		$task->finish($result);
	}
}
