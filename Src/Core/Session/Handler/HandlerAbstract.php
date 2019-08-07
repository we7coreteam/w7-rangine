<?php

namespace W7\Core\Session\Handler;

abstract class HandlerAbstract implements HandlerInterface {
	protected $id;

	public function setId($id) {
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}
}