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

namespace W7\Core\Session\Handler;

abstract class HandlerAbstract implements \SessionHandlerInterface {
	protected $config;
	protected $expires;

	public function __construct($config) {
		$this->config = $config;
		$this->init();
	}

	protected function init() {
	}

	public function getExpires() {
		if ($this->expires === null) {
			$userExpires = (int)($this->config['expires'] ?? ini_get('session.gc_maxlifetime'));
			$this->expires = $userExpires <= 0 ? 0 : $userExpires;
		}
		return $this->expires;
	}

	final public function open($save_path, $name) {
	}

	public function gc($maxlifetime) {
	}

	final public function close() {
	}
}
