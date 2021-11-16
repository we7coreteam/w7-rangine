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
use W7\Core\Message\Message;
use W7\Core\Server\ServerEvent;

class PipeMessageListener extends ListenerAbstract {
	/**
	 * @throws \Exception
	 */
	public function run(...$params) {
		/**
		 * @var Server $server
		 */
		[$server, $workId, $data] = $params;

		$message = Message::unpack($data);

		$this->getEventDispatcher()->dispatch(ServerEvent::ON_USER_AFTER_PIPE_MESSAGE, [$server, $workId, $message, $data]);
	}
}
