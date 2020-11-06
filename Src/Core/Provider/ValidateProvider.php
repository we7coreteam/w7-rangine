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

namespace W7\Core\Provider;

use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;

class ValidateProvider extends ProviderAbstract {
	public function register() {
		$this->registerFactory();
	}

	public function registerFactory() {
		$this->container->set(Factory::class, function () {
			$validate = new Factory($this->container->get('translator'));
			$validate->setPresenceVerifier(new DatabasePresenceVerifier($this->container->get('db-factory')));
			return $validate;
		});
	}

	public function providers(): array {
		return [Factory::class];
	}
}
