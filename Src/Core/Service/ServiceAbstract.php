<?php

namespace W7\Core\Service;

abstract class ServiceAbstract {
	protected $name;

	public function __construct($name = null) {
		if (!$name) {
			$name = get_called_class();
		}
		$this->name = $name;
	}

	public function register() {

	}

	public function boot() {

	}
}