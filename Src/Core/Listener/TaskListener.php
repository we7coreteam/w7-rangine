<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:18
 */

namespace W7\Core\Listener;

use Swoole\Http\Server;
use W7\Core\Base\Dispatcher\TaskDispatcher;
use W7\Core\Base\Listener\ListenerInterface;

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
