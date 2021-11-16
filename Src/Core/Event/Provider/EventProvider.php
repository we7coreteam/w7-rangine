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

namespace W7\Core\Event\Provider;

use Illuminate\Container\Container;
use W7\Contract\Event\EventDispatcherInterface;
use W7\Contract\Queue\QueueFactoryInterface;
use W7\Core\Event\Dispatcher;
use W7\Core\Provider\ProviderAbstract;

class EventProvider extends ProviderAbstract {
	public function register(): void {
		$this->container->set(EventDispatcherInterface::class, function () {
			$eventDispatcher = new Dispatcher(Container::getInstance());

			$events = $this->config->get('event', []);
			foreach ($events as $event => $listeners) {
				$listeners = (array)$listeners;
				foreach ($listeners as $listener) {
					$eventDispatcher->listen($event, $listener);
				}
			}

			Container::getInstance()->instance('events', $eventDispatcher);
			$eventDispatcher->setQueueResolver(function () {
				if ($this->container->has(QueueFactoryInterface::class)) {
					return $this->container->get(QueueFactoryInterface::class);
				}
				return null;
			});

			return $eventDispatcher;
		});
	}

	public function providers(): array {
		return [EventDispatcherInterface::class];
	}
}
