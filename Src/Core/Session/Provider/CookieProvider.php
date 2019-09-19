<?php

namespace W7\Core\Session\Provider;

use W7\Core\Provider\ProviderAbstract;

class CookieProvider extends ProviderAbstract {
	public function register() {
		$this->initCookieEnv();
	}

	private function initCookieEnv() {
		$config = iconfig()->getUserAppConfig('cookie');

		if (isset($config['http_only'])) {
			ini_set('session.cookie_httponly', $config['http_only']);
		}
		if (isset($config['path'])) {
			ini_set('session.cookie_path', $config['path']);
		}
		if (isset($config['domain'])) {
			ini_set('session.cookie_domain', $config['domain']);
		}
		if (isset($config['secure'])) {
			ini_set('session.cookie_secure', $config['secure']);
		}
		$config = iconfig()->getUserAppConfig('session');
		if (isset($config['expires']) && $config['expires'] >= 0) {
			ini_set('session.cookie_lifetime', (int)$config['expires']);
		}
	}
}