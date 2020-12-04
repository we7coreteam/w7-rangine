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
use W7\Fpm\Session\Middleware\SessionMiddleware;
use W7\Fpm\Server\Dispatcher;

class BeforeStartListener extends ListenerAbstract {
	public function run(...$params) {
		$this->initSessionConfig();
		$this->registerMiddleware();
	}

	//把用户设置的session配置起作用到php.ini中
	private function initSessionConfig() {
		$sessionConfig = $this->getConfig()->get('app.session', []);
		if (empty($sessionConfig['save_path']) && (empty($sessionConfig['handler']) || $sessionConfig['handler'] == 'file')) {
			//如果没设置，使用php默认的session目录
			$sessionConfig['save_path'] = session_save_path();
		}
		if (!empty($sessionConfig['name'])) {
			session_name($sessionConfig['name']);
		}
		if (!empty($sessionConfig['gc_divisor'])) {
			ini_set('session.gc_divisor', $sessionConfig['gc_divisor']);
		}
		if (!empty($sessionConfig['gc_probability'])) {
			ini_set('session.gc_probability', $sessionConfig['gc_probability']);
		}
		if (!empty($sessionConfig['expires'])) {
			ini_set('session.gc_maxlifetime', $sessionConfig['expires']);
		}
		ini_set('session.auto_start', 'Off');

		$this->getConfig()->set('app.session', $sessionConfig);
	}

	private function registerMiddleware() {
		/**
		 * @var \W7\Fpm\Server\Dispatcher $dispatcher
		 */
		$dispatcher = $this->getContainer()->singleton(Dispatcher::class);
		$dispatcher->getMiddlewareMapping()->addBeforeMiddleware(SessionMiddleware::class, true);
	}
}
