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

use W7\Http\Message\Server\Response;

class CookieChannel extends ChannelAbstract {
	public function getSessionId() {
		if (!$this->sessionId) {
			$this->sessionId = $this->request->cookie($this->getSessionName(), $this->generateId());
		}

		return $this->sessionId;
	}

	public function replenishResponse(Response $response) : Response {
		return $response->withCookie($this->getSessionName(), $this->getSessionId());
	}
}
