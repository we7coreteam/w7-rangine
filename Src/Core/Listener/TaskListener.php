<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:18
 */

namespace W7\Core\Listener;

use Swoole\Http\Server;

class TaskListener implements ListenerInterface
{
	public function run(Server $server, $taskId, $workerId, $data)
	{
		/**
		 * @var TaskDispatcher $taskExecutor
		 */
		$taskExecutor = iloader()->singleton(TaskDispatcher::class);
		try {
			$result = $taskExecutor->run($data);
		} catch (\Exception $exception) {
			$server->finish($exception->getMessage());
			return;
		}
		$server->finish($result);
	}
}
