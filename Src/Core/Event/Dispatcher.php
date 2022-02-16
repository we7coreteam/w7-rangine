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

namespace W7\Core\Event;

use Exception;
use Illuminate\Events\Dispatcher as DispatcherAbstract;
use Illuminate\Support\Str;
use ReflectionClass;
use W7\Contract\Event\EventDispatcherInterface;
use W7\Contract\Event\ShouldBroadcastInterface;
use W7\Contract\Event\ShouldQueueInterface;

class Dispatcher extends DispatcherAbstract implements EventDispatcherInterface {
	public function listen($events, $listener = null) {
		if (is_string($listener)) {
			$class = $this->parseClassCallable($listener)[0];
			if (!class_exists($class)) {
				return false;
			}
		}

		parent::listen($events, $listener);
	}

	protected function parseClassCallable($listener) {
		return Str::parseCallback($listener, 'run');
	}

	public function createClassListener($listener, $wildcard = false) {
		return function ($event, $payload) use ($listener, $wildcard) {
			if ($wildcard) {
				return call_user_func($this->createClassCallable($listener, $payload), $event, $payload);
			}

			return call_user_func_array(
				$this->createClassCallable($listener, $payload),
				$payload
			);
		};
	}

	protected function createClassCallable($listener, $payload = []) {
		[$class, $method] = $this->parseClassCallable($listener);

		if ($this->handlerShouldBeQueued($class)) {
			return $this->createQueuedHandlerCallable($class, $method);
		}

		return [new $class(...$payload), $method];
	}

	protected function shouldBroadcast(array $payload) {
		return isset($payload[0]) &&
			$payload[0] instanceof ShouldBroadcastInterface &&
			$this->broadcastWhen($payload[0]);
	}

	protected function handlerShouldBeQueued($class) {
		try {
			return (new ReflectionClass($class))->implementsInterface(
				ShouldQueueInterface::class
			);
		} catch (Exception $e) {
			return false;
		}
	}
}
