<?php

namespace W7\Core\Session\Handler;

class RedisHandler implements HandlerInterface {
	public function set($key, $value, $ttl) {
		return icache()->set($key, $value, $ttl);
	}

	public function get($key) {
		return icache()->get($key);
	}

	public function has($key) {
		return icache()->has($key);
	}

	public function clear() {
		return icache()->clear();
	}
}