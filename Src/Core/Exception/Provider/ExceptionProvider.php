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

namespace W7\Core\Exception\Provider;

use W7\Core\Exception\Handler\ExceptionHandler;
use W7\Core\Exception\HandlerExceptions;
use W7\Core\Provider\ProviderAbstract;

class ExceptionProvider extends ProviderAbstract {
	public function register() {
		$userHandler = 'W7\App\Handler\Exception\ExceptionHandler';
		if (class_exists($userHandler)) {
			$handler = new $userHandler();
			if ($handler instanceof ExceptionHandler) {
				iloader()->get(HandlerExceptions::class)->setHandler($handler);
			}
		}
	}
}
