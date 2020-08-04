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

namespace W7\Core\Facades;

use W7\Core\Task\TaskDispatcher;
use W7\Core\Message\Message;
use W7\Core\Message\TaskMessage;

/**
 * Class Task
 * @package W7\Core\Facades
 *
 * @method static mixed dispatchNow($message, $server = null, $taskId = null, $workerId = null)
 * @method static Message handle(...$params)
 */
class Task extends FacadeAbstract {
	protected static function getFacadeAccessor() {
		return TaskDispatcher::class;
	}

	public static function dispatch($taskName, $params = [], int $timeout = null) {
		//构造一个任务消息
		$taskMessage = new TaskMessage();
		$taskMessage->task = $taskName;
		$taskMessage->params = $params;
		$taskMessage->timeout = $timeout;
		$taskMessage->type = TaskMessage::OPERATION_TASK_ASYNC;

		return self::getFacadeRoot()->dispatch($taskMessage);
	}
}
