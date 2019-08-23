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

use W7\App;
use W7\Core\Provider\ProviderAbstract;

class ExceptionProvider extends ProviderAbstract {
	public function register() {
		iloader()->set(ExceptionHandle::class, function () {
			return new ExceptionHandle(App::$server->type);
		});
	}
}
