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

use Swoole\Http\Server;
use Swoole\Server\Task;
use W7\Core\Exception\HandlerExceptions;
use W7\Core\Facades\Container;
use W7\Core\Facades\Event;
use W7\Core\Facades\Task as TaskFacade;
use W7\Core\Message\Message;
use W7\Core\Task\Event\AfterTaskExecutorEvent;
use W7\Core\Task\Event\BeforeTaskExecutorEvent;
use W7\Core\Task\TaskDispatcher;

class TaskListener implements ListenerInterface {
	public function run(...$params) {
		list($server, $task) = $params;

		return $this->dispatchTask($server, $task);
	}

	private function dispatchTask(Server $server, Task $task) {
		$message = Message::unpack($task->data);
		/**
		 * @var TaskDispatcher $taskDispatcher
		 */
		Event::dispatch(new BeforeTaskExecutorEvent($message));
		try {
			$message = TaskFacade::dispatchNow($message, $server, $task->id, $task->worker_id);
			Event::dispatch(new AfterTaskExecutorEvent($message));
		} catch (\Throwable $throwable) {
			$message->result = $throwable->getMessage();
			Event::dispatch(new AfterTaskExecutorEvent($message, $throwable));
			Container::singleton(HandlerExceptions::class)->getHandler()->report($throwable);
		}

		$task->finish($message->result);
	}
}
