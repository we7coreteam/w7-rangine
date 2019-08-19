<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com>
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
		$cookie = Cookie::new([
			'name' => $this->getSessionName(),
			'value' => $this->getSessionId()
		]);

		return $response->withCookie($cookie);
	}
}
