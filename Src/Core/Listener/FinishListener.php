<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:18
 */

namespace W7\Core\Listener;

use W7\Core\Config\Event;

class FinishListener implements ListenerInterface {
	public function run(...$params) {
		list($server, $task_id, $data) = $params;

		ievent(Event::ON_USER_TASK_FINISH, [$data]);
	}
}
