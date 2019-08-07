<?php

namespace W7\Core\Session\Handler;

class RedisHandler extends HandlerAbstract {
	public function set($key, $value, $ttl) {
		$session = icache()->get($this->id);
		$session[$key] = $value;
		return icache()->set($this->id, $session, $ttl);
	}

	public function get($key, $default = '') {
		$session = icache()->get($this->id);
		return $session[$key] ?? $default;
	}

	public function has($key) {
		$session = icache()->get($this->id);
		return isset($session[$key]);
	}

	public function clear() {
		return icache()->delete($this->id);
	}
}