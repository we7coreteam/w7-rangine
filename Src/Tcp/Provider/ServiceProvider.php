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

namespace  W7\Tcp\Provider;

use W7\Core\Provider\ProviderAbstract;
use W7\Tcp\Parser\JsonParser;
use W7\Tcp\Parser\ParserInterface;

class ServiceProvider extends ProviderAbstract {
	public function register() {
		$this->registerDataParser();
	}

	private function registerDataParser() {
		iloader()->set(ParserInterface::class, function () {
			return new JsonParser();
		});
	}
}
