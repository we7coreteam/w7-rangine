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

namespace W7\Core\Exception\Handler;

use Psr\Http\Message\ResponseInterface;
use W7\Core\Exception\ResponseExceptionAbstract;

class ExceptionHandler extends HandlerAbstract {
	public function handle(ResponseExceptionAbstract $e) : ResponseInterface {
		return $e->render();
	}
}