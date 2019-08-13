<?php

namespace W7\Core\Session\Handler;

abstract class HandlerAbstract implements \SessionHandlerInterface {
	protected $config;
	protected static $expires;

	public function __construct($config) {
		$this->config = $config;
		$this->init();
	}

	protected function init(){}

	public function getExpires() {
		if (static::$expires === null) {
			$userExpires = (int)($this->config['expires'] ?? ini_get("session.gc_maxlifetime"));
			static::$expires = $userExpires;
		}
		return static::$expires;
	}

	public function open($save_path, $name) {}

	public function gc($maxlifetime) {}

	public function close() {}
}