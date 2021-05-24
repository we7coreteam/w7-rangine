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

use W7\Core\Message\MessageAbstract;
use W7\Core\Message\TaskMessage;
use W7\Core\Server\ServerEvent;

/**
 * onFinish(\Swoole\Server $serv, int $task_id, string $data)
 */
class FinishListener extends ListenerAbstract {
	public function run(...$params) {
		/**
		 * @var TaskMessage $taskMessage
		 */
		list($server, $task_id, $taskMessage) = $params;

		if (!($taskMessage instanceof MessageAbstract)) {
			throw new \RuntimeException($taskMessage);
		}

		//Process the callback method set in the message. If not specified, see if the task contains the Finish function. Otherwise, what is not executed
		$callback = $taskMessage->getFinishCallback();
		if (!empty($callback)) {
			call_user_func_array($callback, [$server, $task_id, $taskMessage->result, $taskMessage->params]);
		}

		if ($taskMessage->hasFinishCallback) {
			$task = $this->getContainer()->get($taskMessage->task);
			call_user_func_array([$task, 'finish'], [$server, $task_id, $taskMessage->result, $taskMessage->params]);
		}

		$this->getEventDispatcher()->dispatch(ServerEvent::ON_USER_TASK_FINISH, [$taskMessage->result]);
	}
}
