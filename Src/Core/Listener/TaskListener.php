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
use W7\Core\Helper\Traiter\TaskDispatchTrait;
use W7\Core\Message\Message;
use W7\Core\Message\TaskMessage;
use W7\Core\Server\ServerEvent;
use W7\Core\Task\Event\AfterTaskExecutorEvent;
use W7\Core\Task\Event\BeforeTaskExecutorEvent;

class TaskListener extends ListenerAbstract {
	use TaskDispatchTrait;

	public function run(...$params) {
		[$server, $task] = $params;

		return $this->dispatchTask($server, $task);
	}

	private function dispatchTask(Server $server, Task $task) {
		$message = Message::unpack($task->data);

		if ($message instanceof TaskMessage) {
			try {
				$this->getEventDispatcher()->dispatch(new BeforeTaskExecutorEvent($message));

				$message = $this->dispatchNow($message, $server, $task->worker_id, $task->id);

				$this->getEventDispatcher()->dispatch(new AfterTaskExecutorEvent($message));
			} catch (\Throwable $throwable) {
				$message->result = $throwable->getMessage();

				$this->getEventDispatcher()->dispatch(new AfterTaskExecutorEvent($message, $throwable));

				$this->getContainer()->get(HandlerExceptions::class)->getHandler()->report($throwable);
			}

			$task->finish($message);
		}

		$this->getEventDispatcher()->dispatch(ServerEvent::ON_USER_AFTER_TASK, [$server, $task]);
	}
}
