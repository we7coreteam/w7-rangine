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

abstract class HandlerAbstract extends \SessionHandler {
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
			$configExpires = (int)($this->config['expires'] ?? 0);
			$this->expires = $configExpires <= 0 ? ini_get('session.gc_maxlifetime') : $configExpires;
		}
		return $this->expires;
	}

	public function pack($data) {
		return serialize($data);
	}

	public function unpack($data) {
		return unserialize($data);
	}

	public function open($save_path, $name) {
		return true;
	}

	public function close($session_id = '') {
		return true;
	}

	public function create_sid(){
		return session_create_id();
	}
}
