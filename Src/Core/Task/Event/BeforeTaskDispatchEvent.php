<?php

namespace W7\Core\Task\Event;

use W7\Core\Message\TaskMessage;

class BeforeTaskDispatchEvent {
	public TaskMessage $taskMessage;
	public string $channel;

	public function __construct(TaskMessage $taskMessage, $channel = 'default') {
		$this->taskMessage = $taskMessage;
		$this->channel = $channel;
	}
}