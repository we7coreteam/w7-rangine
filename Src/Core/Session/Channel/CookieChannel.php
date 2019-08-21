<?php

/**
 * WeEngine Api System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Core\Session\Channel;

use W7\Http\Message\Base\Cookie;
use W7\Http\Message\Server\Response;

class CookieChannel extends ChannelAbstract {
	public function getSessionId() {
		if (!$this->sessionId) {
			$this->sessionId = $this->request->cookie($this->getSessionName(), $this->generateId());
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
