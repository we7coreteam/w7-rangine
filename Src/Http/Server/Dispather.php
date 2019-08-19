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
