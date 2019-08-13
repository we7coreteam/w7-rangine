<?php

namespace W7\Core\Session\Channel;

use W7\Http\Message\Base\Cookie;
use W7\Http\Message\Server\Response;

class CookieChannel extends ChannelAbstract {
	public function getSessionId() {
		$cookies = $this->request->getCookieParams();
		if (empty($cookies[$this->getSessionName()])) {
			$cookies[$this->getSessionName()] = $this->generateId();
		}

		return $cookies[$this->getSessionName()];
	}

	public function replenishResponse(Response $response) : Response {
		$config = iconfig()->getUserAppConfig('cookie');
		$cookie = Cookie::new([
			'name' => $this->getSessionName(),
			'value' => $this->getSessionId(),
			'expires' => $this->getExpires(),
			'httpOnly' => isset($config['http_only']) ? $config['http_only'] : ini_get('session.cookie_httponly'),
			'path' => isset($config['path']) ? $config['path'] : ini_get('session.cookie_path'),
			'domain' => isset($config['domain']) ? $config['domain'] : ini_get('session.cookie_domain'),
			'secure' => isset($config['secure']) ? $config['secure'] : ini_get('session.cookie_secure'),
		]);

		return $response->withCookie($cookie);
	}
}