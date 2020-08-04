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

use W7\Core\Dispatcher\TaskDispatcher;
use W7\Core\Message\Message;
use W7\Core\Message\TaskMessage;

/**
 * Class Task
 * @package W7\Core\Facades
 *
 * @method static mixed register(...$params)
 * @method static mixed registerCo(TaskMessage $message)
 * @method static Message dispatch(...$params)
 */
class Task extends FacadeAbstract {
	protected static function getFacadeAccessor() {
		return TaskDispatcher::class;
	}

	public static function execute($taskName, $params = [], int $timeout = 3) {
		//构造一个任务消息
		$taskMessage = new TaskMessage();
		$taskMessage->task = $taskName;
		$taskMessage->params = $params;
		$taskMessage->timeout = $timeout;
		$taskMessage->type = TaskMessage::OPERATION_TASK_ASYNC;

		return self::register($taskMessage);
	}

	public static function executeAsync($taskName, $params = [], int $timeout = 3) {
		if (self::getContainer()->has('queue')) {
			$task = new $taskName($params);
			return self::getContainer()->get('queue')->push($task);
		} else {
			//构造一个任务消息
			$taskMessage = new TaskMessage();
			$taskMessage->task = $taskName;
			$taskMessage->params = $params;
			$taskMessage->timeout = $timeout;
			$taskMessage->type = TaskMessage::OPERATION_TASK_CO;
			return self::registerCo($taskMessage);
		}
	}
}
