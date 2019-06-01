<?php

namespace W7\Core\Service;

abstract class ServiceAbstract {
	protected $name;

	public function __construct($name = null) {
		$this->name = $name;
	}

	public function register() {

	}

	public function boot() {

	}
}