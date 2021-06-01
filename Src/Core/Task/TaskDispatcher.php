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

namespace W7\Core\Task;

use Exception;
use W7\App;
use W7\Contract\Task\TaskDispatcherInterface;
use W7\Core\Dispatcher\DispatcherAbstract;
use W7\Core\Exception\TaskException;
use W7\Core\Message\Message;
use W7\Core\Message\TaskMessage;
use W7\Core\Task\Event\AfterTaskDispatchEvent;
use W7\Core\Task\Event\BeforeTaskDispatchEvent;

class TaskDispatcher extends DispatcherAbstract implements TaskDispatcherInterface {
	protected $queueResolver;

	public function setQueueResolver(\Closure $closure) {
		$this->queueResolver = $closure;
	}

	protected function resolveQueue() {
		return call_user_func($this->queueResolver);
	}

	public function dispatch(...$params) {
		/**
		 * @var TaskMessage $message
		 */
		[$message] = $params;

		if (!($message instanceof TaskMessage)) {
			throw new \RuntimeException('Invalid task message');
		}

		if (!class_exists($message->task)) {
			throw new TaskException('Task ' . $message->task . ' not found');
		}

		if ($message->type != TaskMessage::OPERATION_TASK_NOW && (method_exists($message->task, 'isAsyncTask') && $message->task::isAsyncTask())) {
			return $this->dispatchAsync($message);
		}

		if ($message->type == TaskMessage::OPERATION_TASK_NOW || !isWorkerStatus()) {
			$this->eventDispatcher && $this->eventDispatcher->dispatch(new BeforeTaskDispatchEvent($message, 'default'));

			array_shift($params);
			$message = $this->dispatchNow($message, ...$params);

			$this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterTaskDispatchEvent($message, 'default', $message->result));
			return $message;
		}

		$message->type = TaskMessage::OPERATION_TASK_CO;

		$this->eventDispatcher && $this->eventDispatcher->dispatch(new BeforeTaskDispatchEvent($message, 'co'));

		$result = App::$server->getServer()->taskCo($message->pack(), $message->timeout);

		$this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterTaskDispatchEvent($message, 'co', $result));
		return $result;
	}

	public function dispatchAsync(TaskMessage $message) {
		if (!class_exists($message->task)) {
			throw new TaskException('Task ' . $message->task . ' not found');
		}

		if ($this->queueResolver && (method_exists($message->task, 'shouldQueue') && $message->task::shouldQueue())) {
			$queueResolver = $this->resolveQueue();
			if (!$queueResolver) {
				throw new Exception('the message queue resolver for task dispatch is empty');
			}
			$connection = $this->resolveQueue()->connection(
				$message->task::$connection ?? null
			);

			$this->eventDispatcher && $this->eventDispatcher->dispatch(new BeforeTaskDispatchEvent($message, 'queue'));
			$queue = $message->task::$queue ?? null;
			if (isset($message->task::$delay)) {
				$result = $connection->laterOn($queue, $message->task::$delay, new CallQueuedTask($message));
			} else {
				$result = $connection->pushOn($queue, new CallQueuedTask($message));
			}
			$this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterTaskDispatchEvent($message, 'queue', $result));
			return $result;
		}

		if (!isWorkerStatus()) {
			throw new TaskException('Please deliver task at worker process or deliver to queue!');
		}

		$this->eventDispatcher && $this->eventDispatcher->dispatch(new BeforeTaskDispatchEvent($message, 'worker'));
		$result = App::$server->getServer()->task($message->pack());
		$this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterTaskDispatchEvent($message, 'worker', $result));
		return $result;
	}

	protected function dispatchNow($message, $server = null, $taskId = null, $workerId = null) {
		$server = $server ?? (App::$server ? App::$server->getServer() : null);
		$taskId = $taskId ?? $this->getContext()->getCoroutineId();
		$workId = $workerId ?? ($server ? $server->worker_id : $workerId);

		/**
		 * @var TaskMessage $message
		 */
		!is_object($message) && $message = Message::unpack($message);

		if (!class_exists($message->task)) {
			throw new TaskException('Task ' . $message->task . ' not found');
		}

		/**
		 * @var TaskAbstract $task
		 */
		$task = $this->getContainer()->get($message->task);
		if (method_exists($task, 'finish')) {
			$message->hasFinishCallback = true;
		}

		$this->getContext()->setContextDataByKey('workid', $workId);
		$this->getContext()->setContextDataByKey('coid', $taskId);
		try {
			$message->result = call_user_func_array([$task, $message->method], [$server, $taskId, $workId, $message->params ?? []]);
		} catch (\Throwable $e) {
			$message->result = $e->getMessage();
			$task->fail($e);
			throw $e;
		}

		if (!($server && \property_exists($server, 'taskworker') && ($server->taskworker) && $message->isTaskAsync())) {
			$task->finish($server, $taskId, $message->result, $message->params ?? []);
		}

		return $message;
	}
}
