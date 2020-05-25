<?php

namespace W7\Core\Route;

use W7\Core\Route\Validator\ValidatorInterface;

class RouteCollector extends \FastRoute\RouteCollector {
	protected $validators = [];

	public function getCurrentGroupPrefix() {
		return $this->currentGroupPrefix;
	}

	public function registerValidator(ValidatorInterface $validator) {
		$this->validators[] = $validator;
	}

	public function addRoute($httpMethod, $route, $handler) {
		if ($this->validate($httpMethod, $route, $handler)) {
			parent::addRoute($httpMethod, $route, $handler);
		}
	}

	protected function validate($httpMethod, $route, $handler) {
		/**
		 * @var ValidatorInterface $validator
		 */
		foreach ($this->validators as $validator) {
			if (!$validator->match($httpMethod, $route, $handler)) {
				return false;
			}
		}

		return true;
	}
}