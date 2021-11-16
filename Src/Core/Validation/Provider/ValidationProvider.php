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

use Illuminate\Validation\DatabasePresenceVerifier;
use W7\Contract\Translation\TranslatorInterface;
use W7\Contract\Validation\ValidatorFactoryInterface;
use W7\Core\Provider\ProviderAbstract;
use W7\Core\Validation\ValidationFactory;

class ValidationProvider extends ProviderAbstract {
	public function register(): void {
		$this->container->set(ValidatorFactoryInterface::class, function () {
			$validationFactory = new ValidationFactory($this->container->get(TranslatorInterface::class), $this->container);
			$validationFactory->setPresenceVerifier(new DatabasePresenceVerifier($this->container->get('db-factory')));
			return $validationFactory;
		});
	}

	public function providers(): array {
		return [ValidatorFactoryInterface::class];
	}
}
