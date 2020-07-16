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

use W7\Core\Facades\Container;
use W7\Core\Facades\Event;
use W7\Core\Message\MessageAbstract;
use W7\Core\Message\TaskMessage;
use W7\Core\Server\ServerEvent;

/**
 * onFinish(\Swoole\Server $serv, int $task_id, string $data)
 */
class FinishListener implements ListenerInterface {
	public function run(...$params) {
		/**
		 * @var TaskMessage $taskMessage
		 */
		list($server, $task_id, $taskMessage) = $params;

		if (!($taskMessage instanceof MessageAbstract)) {
			throw new \RuntimeException($taskMessage);
		}

		//echo '这里是回调函数' . $task_id . PHP_EOL;
		//处理在消息中设置的回调方法，如果未指定，则看任务中是否包含 finish 函数，否则什么不执行
		$callback = $taskMessage->getFinishCallback();
		if (!empty($callback)) {
			call_user_func_array($callback, [$server, $task_id, $taskMessage->result, $taskMessage->params]);
		}

		if ($taskMessage->hasFinishCallback) {
			$task = Container::singleton($taskMessage->task);
			call_user_func_array([$task, 'finish'], [$server, $task_id, $taskMessage->result, $taskMessage->params]);
		}
		Event::dispatch(ServerEvent::ON_USER_TASK_FINISH, [$taskMessage->result]);
	}
}
