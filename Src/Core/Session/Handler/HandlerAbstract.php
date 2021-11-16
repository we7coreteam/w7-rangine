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
	protected array $config;
	protected int $expires;

	public function __construct(array $config) {
		$this->config = $config;
		$this->init();
	}

	protected function init() {
	}

	public function getExpires(): int {
		if ($this->expires === null) {
			$configExpires = (int)($this->config['expires'] ?? 0);
			$this->expires = $configExpires <= 0 ? (int)ini_get('session.gc_maxlifetime') : $configExpires;
		}
		return $this->expires;
	}

	public function pack($data): string {
		return serialize($data);
	}

	public function unpack($data) {
		return unserialize($data);
	}

	public function open($save_path, $name): bool {
		return true;
	}

	public function close($session_id = ''): bool {
		return true;
	}

	public function create_sid(): string {
		return session_create_id();
	}
}
