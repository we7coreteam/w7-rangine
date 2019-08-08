<?php

namespace W7\Core\Cache\Handler;

class RedisHandler extends HandlerAbstract {
	/**
	 * @var \Redis
	 */
	private $redis;

	public function __construct(\Redis $redis) {
		$this->redis = $redis;
	}

	public function set($key, $value, $ttl = null) {
		$this->redis->set($key, $value, $ttl);
	}

	public function get($key, $default = null) {
		return $this->redis->get($key);
	}

	public function has($key) {
		return $this->redis->exists($key);
	}

	public function setMultiple($values, $ttl = null) {
		return $this->redis->mset($values);
	}

	public function getMultiple($keys, $default = null) {
		return $this->redis->mget($keys);
	}

	public function delete($key) {
		return $this->redis->delete($key);
	}

	public function deleteMultiple($keys) {
		return $this->redis->delete($keys);
	}

	public function clear() {
		return $this->redis->flushDB();
	}

	public function __call($name, $arguments) {
		return $this->redis->$name(...$arguments);
	}
}