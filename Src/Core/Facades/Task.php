<?php

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

	public static function executeInCo($taskName, $params = [], int $timeout = 3) {
		//构造一个任务消息
		$taskMessage = new TaskMessage();
		$taskMessage->task = $taskName;
		$taskMessage->params = $params;
		$taskMessage->timeout = $timeout;
		$taskMessage->type = TaskMessage::OPERATION_TASK_CO;

		return self::registerCo($taskMessage);
	}
}