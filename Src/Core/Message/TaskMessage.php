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

namespace W7\Core\Message;

use W7\App;

class TaskMessage extends MessageAbstract {
	use MessageTraiter;

	public string $messageType = Message::MESSAGE_TYPE_TASK;

	/**
	 * Synchronization task
	 */
	public const OPERATION_TASK_NOW = '0';
	/**
	 * Asynchronous tasks
	 */
	public const OPERATION_TASK_ASYNC = '1';
	/**
	 * Coroutines task
	 */
	public const OPERATION_TASK_CO = '2';

	/**
	 * Task type
	 */
	public string $type;

	/**
	 * @var mixed Task name
	 */
	public mixed $task = '';

	/**
	 * Task timeout, useful only if the coroutine blocks the task asynchronously
	 * @var int
	 */
	public int $timeout = 3;

	/**
	 * Additional parameters
	 * @var array
	 */
	public array $params = [];

	/**
	 * When a task is dispatched, specify the default method in the task
	 */
	public string $method = 'run';

	/**
	 * Save the result of task execution,
	 * Because you need to continue passing the message to the onFinish event
	 * In the onFinish event, you need to handle callbacks and other work
	 * @var array
	 */
	public array $result = [];

	/**
	 * Whether to include the call back to the Finish function
	 * @var bool
	 */
	public bool $hasFinishCallback = false;

	public function isTaskAsync(): bool {
		return $this->type === self::OPERATION_TASK_ASYNC;
	}

	public function isTaskCo(): bool {
		return $this->type === self::OPERATION_TASK_CO;
	}

	public function setFinishCallback($class, $method): void {
		$this->params['finish'] =  [$class, $method];
	}

	public function getFinishCallback(): array {
		$callback = $this->params['finish'] ?? null;
		if (empty($callback)) {
			return [null, null];
		}

		if (!class_exists($callback[0])) {
			return [null, null];
		}

		$object = App::getApp()->getContainer()->get($callback[0]);
		if (!method_exists($object, $callback[1])) {
			return [null, null];
		}

		return [$object, $callback[1]];
	}
}
