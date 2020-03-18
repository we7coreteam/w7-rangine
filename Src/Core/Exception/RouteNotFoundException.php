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

namespace W7\Core\Exception;

use Throwable;

class RouteNotFoundException extends ResponseExceptionAbstract {
	public function __construct($message = 'Route not found', $code = 404, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
