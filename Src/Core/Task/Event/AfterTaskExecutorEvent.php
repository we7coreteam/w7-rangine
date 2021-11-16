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

namespace W7\Core\Task\Event;

use W7\Core\Message\TaskMessage;

class AfterTaskExecutorEvent {
	public TaskMessage $taskMessage;
	public ?\Throwable $throwable;

	public function __construct(TaskMessage $taskMessage, \Throwable $throwable = null) {
		$this->taskMessage = $taskMessage;
		$this->throwable = $throwable;
	}
}