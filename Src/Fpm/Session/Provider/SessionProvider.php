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

namespace W7\Fpm\Session\Provider;

use W7\Core\Provider\ProviderAbstract;
use W7\Fpm\Server\Dispatcher;
use W7\Fpm\Session\Middleware\SessionMiddleware;

class SessionProvider extends ProviderAbstract {
	public function register() {
		$this->initSessionConfig();
		$this->registerMiddleware();
	}

	//把用户设置的session配置起作用到php.ini中
	private function initSessionConfig() {
		$appConfig = $this->config->getUserConfig('app');
		$sessionConfig = $appConfig['session'] ?? [];
		if (!empty($sessionConfig['save_path'])) {
			session_save_path($sessionConfig['save_path']);
		} else {
			//如果没设置，使用php默认的session目录
			$sessionConfig['save_path'] = session_save_path();
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

		$appConfig['session'] = $sessionConfig;
		iconfig()->setUserConfig('app', $appConfig);
	}

	private function registerMiddleware() {
		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = icontainer()->singleton(Dispatcher::class);
		$dispatcher->getMiddlewareMapping()->addBeforeMiddleware(SessionMiddleware::class);
	}
}
