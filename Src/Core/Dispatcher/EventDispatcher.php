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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class EventDispatcher extends Dispatcher {
	public function __construct() {
		$this->register();
	}

	public function register() {
		$this->autoRegisterEvents(APP_PATH . '/Event', 'W7\\App');
	}

	public function autoRegisterEvents($path, $classNamespace) {
		if (!file_exists($path)) {
			return false;
		}
		$events = $this->findEvents($path, $classNamespace);
		foreach ($events as $event => $listener) {
			$this->listen($event, $listener);
		}
	}

	/**
	 * 自动发现event和listener 如 app/Event/TestEvent.php 对应app/Listener/TestListener.php. app/Event/Test/TestEvent.php 对应app/Listener/Test/TestListener.php
	 * @param $path
	 * @param $classNamespace
	 * @return array
	 */
	private function findEvents($path, $classNamespace) {
		$events = [];

		$files = Finder::create()
			->in($path)
			->files()
			->ignoreDotFiles(true)
			->name('/^[\w\W\d]+Event.php$/');

		/**
		 * @var SplFileInfo $file
		 */
		foreach ($files as $file) {
			$eventName = $file->getRelativePathname();
			$eventName = substr($eventName, 0, strlen($eventName) - 9);
			$eventName = str_replace('/', '\\', $eventName);
			$listenerClass = $classNamespace . '\\Listener\\' . $eventName . 'Listener';
			if (class_exists($listenerClass)) {
				$eventClass = $classNamespace . '\\Event\\' . $eventName . 'Event';
				$events[$eventClass] = $listenerClass;
			}
		}
		
		return $events;
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
