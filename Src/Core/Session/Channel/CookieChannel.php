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

use Psr\Http\Message\ResponseInterface;
use W7\Http\Message\Base\Cookie;

class CookieChannel extends ChannelAbstract {
	public function getSessionId() : string {
		return $this->request->cookie($this->getSessionName(), '');
	}

	public function replenishResponse(ResponseInterface $response, $sessionId) : ResponseInterface {
		$sessionCookie = Cookie::create(
			$this->getSessionName(),
			$sessionId,
			null,
			null,
			null,
			null,
			null,
			false,
			''
		);
		return $response->withHeader('Set-Cookie', $sessionCookie->__toString());
	}
}
