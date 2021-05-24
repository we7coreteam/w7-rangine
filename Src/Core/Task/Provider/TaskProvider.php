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

namespace W7\Core\Task\Provider;

use W7\Contract\Queue\QueueFactoryInterface;
use W7\Contract\Task\TaskDispatcherInterface;
use W7\Core\Provider\ProviderAbstract;
use W7\Core\Task\TaskDispatcher;

class TaskProvider extends ProviderAbstract {
	public function register() {
		$this->container->singleton(TaskDispatcherInterface::class, function () {
			$taskDispatcher = new TaskDispatcher();

			$taskDispatcher->setQueueResolver(function () {
				if ($this->container->has(QueueFactoryInterface::class)) {
					return $this->container->get(QueueFactoryInterface::class);
				}
			});

			return $taskDispatcher;
		});
	}

	public function providers(): array {
		return [TaskDispatcherInterface::class];
	}
}
