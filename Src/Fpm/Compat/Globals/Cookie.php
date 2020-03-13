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

namespace W7\Fpm\Compat\Globals;

use Psr\Http\Message\ServerRequestInterface;
use W7\Fpm\Compat\Proxy;

class Cookie extends Proxy {
	public function toArray(): array {
		return $this->getRequest()->getCookieParams();
	}

	protected function override(ServerRequestInterface $request, array $cookies): ServerRequestInterface {
		return $request->withCookieParams($cookies);
	}
}
