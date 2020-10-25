<?php

namespace W7\Core\Task\Event;

use W7\Core\Message\TaskMessage;

class BeforeTaskDispatchEvent {
	/**
	 * @var TaskMessage
	 */
	public $taskMessage;
	/**
	 * task dispatch channel
	 */
	public $channel;

	public function __construct(TaskMessage $taskMessage, $channel = 'default') {
		$this->taskMessage = $taskMessage;
		$this->channel = $channel;
	}
}