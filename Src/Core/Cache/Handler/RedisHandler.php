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
use W7\Contract\Redis\RedisFactoryInterface;
use W7\Core\Redis\ConnectionResolver;

class RedisHandler extends HandlerAbstract {
	/**
	 * @var Connection
	 */
	protected $storage;

	public function __construct($storage) {
		$this->storage = $storage;
	}

	public static function connect($config) : HandlerAbstract {
		/**
		 * @var ConnectionResolver $redisManager
		 */
		$redisManager = App::getApp()->getContainer()->get(RedisFactoryInterface::class);
		return new static($redisManager->connection($config['connection'] ?? ''));
	}

	public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool {
		if ($ttl) {
			return $this->storage->set($key, $value, 'EX', $ttl);
		}
		return $this->storage->set($key, $value);
	}

	public function get(string $key, mixed $default = null): mixed {
		return $this->storage->get($key);
	}

	public function has(string $key): bool {
		return $this->storage->exists($key);
	}

	public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null) : bool {
		if ($ttl <= 0) {
			return $this->storage->mset((array)$values);
		}

		$pipeline = $this->storage->multi(\Redis::MULTI);
		foreach ($values as $key => $value) {
			$pipeline->set($key, $value, $ttl);
		}
		$result = $pipeline->exec();

		return count(array_unique($result)) == 1;
	}

	public function getMultiple(iterable $keys, mixed $default = null): iterable {
		return $this->storage->mget((array)$keys);
	}

	public function delete(string $key): bool {
		return $this->storage->del($key);
	}

	public function deleteMultiple(iterable $keys): bool {
		return $this->storage->del(...$keys);
	}

	public function clear(): bool {
		return $this->storage->flushDB();
	}

	public function alive(): bool {
		return $this->storage->ping();
	}

	public function __call($name, $arguments) {
		return $this->storage->$name(...$arguments);
	}
}
