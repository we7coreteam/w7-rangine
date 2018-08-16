<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:18
 */

namespace W7\Core\Listener;

use Swoole\Http\Server;
use W7\Core\Dispatcher\TaskDispatcher;

class TaskListener implements ListenerInterface
{
	public function run(Server $server, $taskId, $workerId, $data) {
		/**
		 * @var TaskDispatcher $taskDispatcher
		 */
		$taskDispatcher = iloader()->singleton(TaskDispatcher::class);
		try {
			$result = $taskDispatcher->dispatch($data, $taskId, $workerId);
		} catch (\Exception $exception) {
			$server->finish($exception->getMessage());
			return;
		}
		if (empty($result)) {
			$result = true;
		}
		$server->finish($result);
	}
}
