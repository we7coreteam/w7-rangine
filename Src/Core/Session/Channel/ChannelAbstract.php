<?php

namespace W7\Core\Session\Channel;

use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;

abstract class ChannelAbstract {
	protected $config;
	protected static $sessionName;
	protected $expires;
	/**
	 * @var Request
	 */
	protected $request;

	public function __construct($config, Request $request) {
		$this->config = $config;
		$this->request = $request;
	}

	protected function getSessionName() {
		if (!static::$sessionName) {
			static::$sessionName = $this->config['name'] ?? session_name();
		}
		return static::$sessionName;
	}

	protected function getExpires() {
		if ($this->expires === null) {
			$userExpires = (int)($this->config['expires'] ?? ini_get("session.gc_maxlifetime"));
			$this->expires = time() + $userExpires;
		}
		return $this->expires;
	}

	protected function generateId() {
		return \session_create_id();
	}

	abstract function getSessionId();

	abstract function replenishResponse(Response $response) : Response;
}