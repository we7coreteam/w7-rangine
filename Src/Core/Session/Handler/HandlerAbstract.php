<?php

namespace W7\Core\Session\Handler;

abstract class HandlerAbstract implements HandlerInterface {
	protected $id;
	protected $config;

	public function __construct($config) {
		$this->config = $config;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}
}