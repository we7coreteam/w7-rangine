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

namespace W7\Core\Session\Channel;

use W7\Http\Message\Base\Cookie;
use W7\Http\Message\Server\Response;

class CookieChannel extends ChannelAbstract {
	public function getSessionId() {
		if (!$this->sessionId) {
			$cookies = $this->request->getCookieParams();
			if (empty($cookies[$this->getSessionName()])) {
				$cookies[$this->getSessionName()] = $this->generateId();
			}
			$this->sessionId = $cookies[$this->getSessionName()];
		}

		return $this->sessionId;
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
