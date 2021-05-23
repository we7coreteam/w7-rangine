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

namespace W7\Core\Validation\Provider;

use Illuminate\Container\Container;
use Illuminate\Validation\DatabasePresenceVerifier;
use W7\Contract\Translation\TranslatorInterface;
use W7\Contract\Validation\ValidatorFactoryInterface;
use W7\Core\Provider\ProviderAbstract;
use W7\Core\Validation\ValidationFactory;

class ValidationProvider extends ProviderAbstract {
	public function register() {
		$this->container->singleton(ValidatorFactoryInterface::class, function () {
			$validationFactory = new ValidationFactory($this->container->singleton(TranslatorInterface::class), $this->container->singleton(Container::class));
			$validationFactory->setPresenceVerifier(new DatabasePresenceVerifier($this->container->singleton('db-factory')));
			return $validationFactory;
		});
	}

	public function providers(): array {
		return [ValidatorFactoryInterface::class];
	}
}
