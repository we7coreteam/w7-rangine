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

namespace W7\Http\Provider;

use W7\Core\Provider\ProviderAbstract;
use W7\Http\Message\Formatter\JsonResponseFormatter;
use W7\Http\Message\Formatter\ResponseFormatterInterface;

class ResponseProvider extends ProviderAbstract {
	public function register() {
		iloader()->set(ResponseFormatterInterface::class, function () {
			return new JsonResponseFormatter();
		});
	}
}
