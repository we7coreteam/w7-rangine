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

namespace W7\Fpm\Session\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\Contract\Session\SessionInterface;
use W7\Core\Middleware\MiddlewareAbstract;

class SessionMiddleware extends MiddlewareAbstract {
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

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$this->initSessionConfig();

		$request->session = $this->getContainer()->clone(SessionInterface::class);
		$request->session->start($request, true);
		$request->session->gc();

		$this->getContext()->setResponse($request->session->replenishResponse($this->getContext()->getResponse()));

		return $handler->handle($request);
	}
}
