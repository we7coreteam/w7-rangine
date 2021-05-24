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

	private function initSessionConfig() {
		$sessionConfig = $this->getConfig()->get('app.session', []);
		$cookieConfig = $this->getConfig()->get('app.cookie', []);
		if (empty($sessionConfig['save_path']) && (empty($sessionConfig['handler']) || $sessionConfig['handler'] == 'file')) {
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
			ini_set('session.cookie_lifetime', $sessionConfig['expires']);
		}
		if (!empty($cookieConfig['domain'])) {
			ini_set('session.cookie_domain', $cookieConfig['domain']);
		}
		if (isset($cookieConfig['secure'])) {
			ini_set('session.cookie_secure', $cookieConfig['secure']);
		}
		if (!empty($cookieConfig['path'])) {
			ini_set('session.cookie_path', $cookieConfig['path']);
		}
		if (isset($cookieConfig['http_only'])) {
			ini_set('session.cookie_httponly', $cookieConfig['http_only']);
		}
		ini_set('session.serialize_handler', 'php_serialize');

		$sessionConfig['prefix'] = '';
		$this->getConfig()->set('app.session', $sessionConfig);
	}

	private function registerMiddleware() {
		/**
		 * @var \W7\Fpm\Server\Dispatcher $dispatcher
		 */
		$dispatcher = $this->getContainer()->get(Dispatcher::class);
		$dispatcher->getMiddlewareMapping()->addBeforeMiddleware(SessionMiddleware::class, true);
	}
}
