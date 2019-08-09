<?php

namespace W7\Core\Session\Channel;

use W7\Http\Message\Server\Request;

abstract class ChannelAbstract {
	protected $sessionName;
	/**
	 * @var Request
	 */
	protected $request;

	public function __construct(Request $request ,$sessionName) {
		$this->request = $request;
		$this->sessionName = $sessionName;
	}

	public function getSessionName() {
		return $this->sessionName;
	}

	protected function generateId() {
		return \session_create_id();
	}

	abstract function getId();
}