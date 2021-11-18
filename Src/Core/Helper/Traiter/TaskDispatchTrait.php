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

namespace W7\Core\Helper\Traiter;

use Illuminate\Contracts\Container\BindingResolutionException;
use W7\App;
use W7\Contract\Task\TaskDispatcherInterface;
use W7\Core\Message\TaskMessage;

trait TaskDispatchTrait {
	/**
	 * @throws \ReflectionException
	 * @throws BindingResolutionException
	 */
	public function dispatchNow(TaskMessage $message, $server = null, $workerId = null, $taskId = null) {
		/**
		 * @var TaskDispatcherInterface $taskDispatcher
		 */
		$taskDispatcher = App::getApp()->getContainer()->get(TaskDispatcherInterface::class);
		$message->type = TaskMessage::OPERATION_TASK_NOW;
		return $taskDispatcher->dispatch($message, $server, $taskId, $workerId);
	}
}
