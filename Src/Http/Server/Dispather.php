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

namespace W7\Http\Server;

use W7\Core\Dispatcher\RequestDispatcher;
use W7\Core\Session\Middleware\SessionMiddleware;
use W7\Http\Middleware\CookieMiddleware;

class Dispather extends RequestDispatcher {
	public $beforeMiddleware = [
		[CookieMiddleware::class],
		[SessionMiddleware::class]
	];
}
