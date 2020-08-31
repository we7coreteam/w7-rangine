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

use W7\Core\Provider\ProviderAbstract;
use W7\Core\Task\TaskDispatcher;

class TaskProvider extends ProviderAbstract {
	public function register() {
		$this->container->set(TaskDispatcher::class, function () {
			$taskDispatcher = new TaskDispatcher();

			if ($this->container->has('queue')) {
				$taskDispatcher->setQueueResolver(function () {
					return $this->container->singleton('queue');
				});
			}

			return $taskDispatcher;
		});
	}

	public function providers(): array {
		return [TaskDispatcher::class];
	}
}
