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

namespace W7\Fpm\Listener;

use W7\Core\Listener\ListenerAbstract;
use W7\Core\Session\SessionTrait;
use W7\Fpm\Session\Middleware\SessionMiddleware;
use W7\Fpm\Server\Dispatcher;

class BeforeStartListener extends ListenerAbstract {
	use SessionTrait;

	public function run(...$params) {
		if ($this->sessionIsAutoStart()) {
			$this->registerMiddleware();
		}
	}

	private function registerMiddleware() {
		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = $this->getContainer()->get(Dispatcher::class);
		$dispatcher->getMiddlewareMapping()->addBeforeMiddleware(SessionMiddleware::class, true);
	}
}
