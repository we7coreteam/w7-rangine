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

use W7\App;
use W7\Contract\Task\TaskDispatcherInterface;
use W7\Core\Dispatcher\DispatcherAbstract;
use W7\Core\Exception\TaskException;
use W7\Core\Message\Message;
use W7\Core\Message\TaskMessage;
use W7\Core\Task\Event\AfterTaskDispatchEvent;
use W7\Core\Task\Event\BeforeTaskDispatchEvent;

/**
 * 派发任务的时候，需要先注册任务，然后在OnTask事件中具体调用
 * Class TaskDispatcher
 * @package W7\Core\Helper\Dispather
 */
class TaskDispatcher extends DispatcherAbstract implements TaskDispatcherInterface {
	protected $queueResolver;

	public function setQueueResolver(\Closure $closure) {
		$this->queueResolver = $closure;
	}

	protected function resolveQueue() {
		return call_user_func($this->queueResolver);
	}

	/**
	 * 派发任务
	 * @param mixed ...$params
	 * @return mixed|void
	 * @throws TaskException
	 * @throws \Throwable
	 */
	public function dispatch(...$params) {
		/**
		 * @var TaskMessage $message
		 */
		list($message) = $params;

		if (!($message instanceof TaskMessage)) {
			throw new \RuntimeException('Invalid task message');
		}

		if (!class_exists($message->task)) {
			throw new TaskException('Task ' . $message->task . ' not found');
		}

		if ($message->isTaskAsync() || method_exists($message->task, 'isAsyncTask') && $message->task::isAsyncTask()) {
			return $this->dispatchAsync($message);
		}

		if (!isWorkerStatus()) {
			$this->eventDispatcher && $this->eventDispatcher->dispatch(new BeforeTaskDispatchEvent($message, 'default'));
			$message = $this->dispatchNow($message);
			$this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterTaskDispatchEvent($message, 'default', $message->result));
			return $message;
		}

		$message->type = TaskMessage::OPERATION_TASK_CO;
		$this->eventDispatcher && $this->eventDispatcher->dispatch(new BeforeTaskDispatchEvent($message, 'co'));
		$result = App::$server->getServer()->taskCo($message->pack(), $message->timeout);
		$this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterTaskDispatchEvent($message, 'co', $result));
		return $result;
	}

	/**
	 * 派发异步任务
	 * @param TaskMessage $message
	 * @return mixed
	 * @throws TaskException
	 */
	public function dispatchAsync(TaskMessage $message) {
		if (!class_exists($message->task)) {
			throw new TaskException('Task ' . $message->task . ' not found');
		}

		if ($this->queueResolver && (method_exists($message->task, 'shouldQueue') && $message->task::shouldQueue())) {
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

	/**
	 * @param $message
	 * @param null $server
	 * @param null $taskId
	 * @param null $workerId
	 * @return TaskMessage
	 * @throws TaskException
	 * @throws \Throwable
	 */
	public function dispatchNow($message, $server = null, $taskId = null, $workerId = null) {
		$server = $server ?? App::$server->getServer();
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
		$task = $this->getContainer()->singleton($message->task);
		if (method_exists($task, 'finish')) {
			$message->hasFinishCallback = true;
		}

		$this->getContext()->setContextDataByKey('workid', $workId);
		$this->getContext()->setContextDataByKey('coid', $taskId);
		try {
			$message->result = call_user_func_array([$task, $message->method], [$server, $taskId, $workId, $message->params ?? []]);
		} catch (\Throwable $e) {
			$task->fail($e);
			throw $e;
		}

		//return 时将消息传递给 onFinish 事件
		//在task进程中执行完成后,onFinish 回调还需要处理一下用户定义的任务回调方法
		if (!($server && \property_exists($server, 'taskworker') && ($server->taskworker) && $message->isTaskAsync())) {
			$task->finish($server, $taskId, $message->result, $message->params ?? []);
		}

		return $message;
	}
}
