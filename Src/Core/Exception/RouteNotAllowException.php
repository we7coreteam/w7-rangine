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

class RouteNotAllowException extends ResponseExceptionAbstract {
	public function __construct($message = 'Route not allowed', $code = 405, \Throwable $previous = null) {
		parent::__construct(json_encode(['error' => $message], JSON_UNESCAPED_UNICODE), $code, $previous);
	}
}
