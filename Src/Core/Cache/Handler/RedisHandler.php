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

use Illuminate\Redis\Connections\Connection;
use W7\App;
use W7\Core\Redis\ConnectionResolver;

class RedisHandler extends HandlerAbstract {
	/**
	 * @var Connection
	 */
	protected $storage;

	public static function connect($config) : HandlerAbstract {
		/**
		 * @var ConnectionResolver $redisManager
		 */
		$redisManager = App::getApp()->getContainer()->get('redis');
		return new static($redisManager->connection($config['name'] ?? ''));
	}

	public function set($key, $value, $ttl = null) {
		return $this->storage->set($key, $value, $ttl);
	}

	public function get($key, $default = null) {
		return $this->storage->get($key);
	}

	public function has($key) {
		return $this->storage->exists($key);
	}

	public function setMultiple($values, $ttl = null) {
		return $this->storage->mset($values);
	}

	public function getMultiple($keys, $default = null) {
		return $this->storage->mget($keys);
	}

	public function delete($key) {
		return $this->storage->del($key);
	}

	public function deleteMultiple($keys) {
		return $this->storage->del(...$keys);
	}

	public function clear() {
		return $this->storage->flushDB();
	}

	public function alive() {
		return $this->storage->ping();
	}

	public function __call($name, $arguments) {
		return $this->storage->$name(...$arguments);
	}
}
