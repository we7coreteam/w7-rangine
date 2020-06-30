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
use W7\Core\Facades\Event;
use W7\Core\Facades\Task;
use W7\Core\Message\Message;
use W7\Core\Message\TaskMessage;
use W7\Core\Server\ServerEvent;

class PipeMessageListener extends ListenerAbstract {
	public function run(...$params) {
		/**
		 * @var Server $server
		 */
		list($server, $workId, $data) = $params;

		//管道不一定只能发送任务消息，需要先判断一下
		$message = Message::unpack($data);

		if ($message instanceof TaskMessage) {
			if ($message->isTaskAsync()) {
				Task::execute($message->task, $message->params);
			}
		}

		Event::dispatch(ServerEvent::ON_USER_AFTER_PIPE_MESSAGE, [$server, $workId, $message, $data]);
	}
}
