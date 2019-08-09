<?php


namespace W7\Core\Session\Handler;


class MemoryHandler extends HandlerAbstract
{
	static private $sessionStore = [];

	public function set($key, $value, $ttl) {
		if (!isset(static::$sessionStore[$this->id])) {
			static::$sessionStore[$this->id] = [];
		}
		static::$sessionStore[$this->id][$key] = [
			'data' => $value,
			'expire' => time() + $ttl,
		];
		return true;
	}

	public function get($key, $default = '') {
		if (empty(static::$sessionStore[$this->id])) {
			return $default;
		}
		if (isset(static::$sessionStore[$this->id][$key]) && static::$sessionStore[$this->id][$key]['expire'] > time()) {
			return static::$sessionStore[$this->id][$key]['data'];
		} else {
			return $default;
		}
	}

	public function has($key) {
		return isset(static::$sessionStore[$this->id][$key]);
	}

	public function destroy() {
		static::$sessionStore[$this->id] = [];
		return true;
	}
}