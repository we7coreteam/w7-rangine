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

namespace W7\Core\Events\Provider;

use Illuminate\Container\Container;
use W7\Core\Events\Dispatcher;
use W7\Core\Provider\ProviderAbstract;

class EventProvider extends ProviderAbstract {
	public function register() {
		$this->container->set(Dispatcher::class, function () {
			$eventDispatcher = new Dispatcher();

			$events = $this->config->get('event', []);
			foreach ($events as $event => $listeners) {
				$listeners = (array)$listeners;
				foreach ($listeners as $listener) {
					$eventDispatcher->listen($event, $listener);
				}
			}

			/**
			 * @var Container $container
			 */
			$container = $this->container->get(Container::class);
			$container->instance('events', $eventDispatcher);
			$eventDispatcher->setContainer($container);
			$eventDispatcher->setQueueResolver(function () {
				if ($this->container->has('queue')) {
					return $this->container->singleton('queue');
				}
				return null;
			});

			return $eventDispatcher;
		});
	}
}
