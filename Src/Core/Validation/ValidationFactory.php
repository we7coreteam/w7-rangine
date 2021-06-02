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

namespace W7\Core\Validation;

use Illuminate\Validation\Factory;
use W7\Contract\Validation\ValidatorFactoryInterface;
use W7\Contract\Validation\ValidatorInterface;

class ValidationFactory extends Factory implements ValidatorFactoryInterface {
	public function make(array $data, array $rules, array $messages = [], array $customAttributes = []): ValidatorInterface {
		/**
		 * @var ValidatorInterface $validator
		 */
		return parent::make($data, $rules, $messages, $customAttributes);
	}

	protected function resolve(array $data, array $rules, array $messages, array $customAttributes) {
		if (is_null($this->resolver)) {
			return new Validator($this->translator, $data, $rules, $messages, $customAttributes);
		}

		return call_user_func($this->resolver, $this->translator, $data, $rules, $messages, $customAttributes);
	}
}
