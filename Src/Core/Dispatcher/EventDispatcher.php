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

namespace W7\Core\Dispatcher;

use Illuminate\Events\Dispatcher;

class EventDispatcher extends Dispatcher {
	public function __construct() {
		$this->register();
	}

	public function register() {
		$events = iconfig()->getUserConfig('event');
		foreach ($events as $event => $listeners) {
			$listeners = (array)$listeners;
			foreach ($listeners as $listener) {
				$this->listen($event, $listener);
			}
		}
	}

	public function listen($events, $listener) {
		if (is_string($listener)) {
			$listener = function () use ($listener) {
				if (class_exists($listener)) {
					return (new $listener)->run(...func_get_args());
				}
				return null;
			};
		}
		parent::listen($events, $listener);
	}

	public function setContainer($container) {
		$this->container = $container;
	}
}
