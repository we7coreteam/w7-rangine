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

namespace W7\Core\Cache\Handler;

class RedisHandler extends HandlerAbstract {
	/**
	 * @var \Redis
	 */
	private $redis;

	public static function getHandler($config) : HandlerAbstract {
		$redis  = new \Redis();
		$result = $redis->connect($config['host'], $config['port'], $config['timeout']);
		if ($result === false) {
			$error = sprintf('Redis connection failure host=%s port=%d', $config['host'], $config['port']);
			throw new \RuntimeException($error);
		}
		if (!empty($config['password'])) {
			$redis->auth($config['password']);
		}
		if (!empty($config['database'])) {
			$redis->select(intval($config['database']));
		}
		return new static($redis);
	}

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

	public function alive() {
		return $this->redis->ping();
	}

	public function __call($name, $arguments) {
		return $this->redis->$name(...$arguments);
	}
}
