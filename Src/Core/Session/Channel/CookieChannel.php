<?php

namespace W7\Core\Session\Channel;

use W7\Core\Session\Session;
use W7\Http\Message\Base\Cookie;
use W7\Http\Message\Server\Response;

class CookieChannel extends ChannelAbstract {
	public function getId() {
		$cookies = $this->request->getCookieParams();
		if (empty($cookies[$this->getSessionName()])) {
			$cookies[$this->getSessionName()] = $this->generateId();
		}

		return $cookies[$this->getSessionName()];
	}

	public function replenishResponse(Response $response, Session $session) : Response {
		$config = $session->getConfig();
		$cookie = Cookie::new([
			'name' => $session->getName(),
			'value' => $session->getId(),
			'expires' => $session->getExpires(),
			'httpOnly' => isset($config['http_only']) ? $config['http_only'] : true,
			'path' => isset($config['path']) ? $config['path'] : '/',
			'domain' => isset($config['domain']) ? $config['domain'] : '',
			'secure' => isset($config['secure']) ? $config['secure'] : false,
		]);

		return $response->withCookie($cookie);
	}
}