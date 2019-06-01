<?php

namespace W7\Core\Controller;

use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use W7\Core\Service\ServiceAbstract;

class ValidateRegister extends ServiceAbstract {
	public function register() {
		iloader()->set(Factory::class, function () {
			return new Factory(new Translator(new ArrayLoader(), 'zh-CN'));
		});
	}
}