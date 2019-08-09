<?php

namespace W7\Core\Session\Handler;

class MemoryHandler extends HandlerAbstract {
	static private $sessionStore = [];

	public function set($key, $value, $ttl) {
		if (!isset(static::$sessionStore[$this->getId()])) {
			static::$sessionStore[$this->getId()] = [];
		}
		static::$sessionStore[$this->getId()][$key] = [
			'data' => $value,
			'expire' => time() + $ttl,
		];
		return true;
	}

	public function get($key, $default = '') {
		if (empty(static::$sessionStore[$this->getId()])) {
			return $default;
		}
		if (isset(static::$sessionStore[$this->getId()][$key]) && static::$sessionStore[$this->getId()][$key]['expire'] > time()) {
			return static::$sessionStore[$this->getId()][$key]['data'];
		} else {
			return $default;
		}
	}

	public function has($key) {
		return isset(static::$sessionStore[$this->getId()][$key]);
	}

	public function destroy() {
		static::$sessionStore[$this->getId()] = [];
		return true;
	}
}