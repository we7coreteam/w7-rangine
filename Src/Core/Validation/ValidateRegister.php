<?php

namespace W7\Core\Validation;

use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;
use W7\Core\Service\ServiceAbstract;

class ValidateRegister extends ServiceAbstract {
	public function register() {
		iloader()->set('validator', function () {
			$factory = new Factory(iloader()->get('translator'));
			$factory->setPresenceVerifier(new DatabasePresenceVerifier(idb()));

			return $factory;
		});
	}
}