<?php
/**
 * @author donknap
 * @date 18-11-24 下午10:03
 */

namespace W7\Core\Listener;


use Swoole\Http\Server;
use W7\Core\Message\WorkerMessage;

class PipeMessageListener extends ListenerAbstract {
	public function run(...$params) {
		/**
		 * @var Server $server
		 */
		list($server, $workId, $data) = $params;
		$message = WorkerMessage::unpack($data);

		if ($message->isTaskAsync()) {
			itask($message->data);
		}
	}
}