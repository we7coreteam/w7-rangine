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
	protected $storage;

	public static function connect($config) : HandlerAbstract {
		if (!empty($config['cluster']['enable'])) {
			$redis = static::createRedisClusterInstance($config);
		} elseif (!empty($config['cluster']['enable'])) {
			$redis = static::createRedisSentinel($config);
		} else {
			$redis = static::createRedis($config);
		}

		$options = $config['options'] ?? [];
		foreach ($options as $name => $value) {
			$redis->setOption($name, $value);
		}

		if (!empty($config['database'])) {
			$redis->select(intval($config['database']));
		}
		return new static($redis);
	}

	protected static function createRedis(array $config) {
		$redis = new \Redis();

		$persistent = $config['persistent'] ?? false;
		$parameters = [
			(string) $config['host'],
			(int) $config['port'],
			$config['timeout'] ?? 0,
			null,
			$config['retry_interval'] ?? 0,
		];

		if (version_compare(phpversion('redis'), '3.1.3', '>=')) {
			$parameters[] = $config['read_timeout'] ?? 0;
		}

		if (version_compare(phpversion('redis'), '5.3.0', '>=')) {
			$config['context'] = $config['context'] ?? null;
			if (!is_null($config['context'])) {
				$parameters[] = $config['context'];
			}
		}

		if (!$redis->{($persistent ? 'pconnect' : 'connect')}(...$parameters)) {
			$error = sprintf('Redis connection failure host=%s port=%d', $config['host'], $config['port']);
			throw new \RuntimeException($error);
		}

		if (!empty($config['password'])) {
			$redis->auth($config['password']);
		}

		return $redis;
	}

	protected static function createRedisClusterInstance(array $config) {
		$parameters = [
			null,
			$config['cluster']['servers'] ?? [],
			$config['cluster']['timeout'] ?? null,
			$config['cluster']['read_timeout'] ?? null,
			$config['cluster']['persistent'] ?? false
		];

		if (version_compare(phpversion('redis'), '4.3.0', '>=')) {
			$parameters[] = $options['password'] ?? null;
		}

		if (version_compare(phpversion('redis'), '5.3.2', '>=')) {
			$config['cluster']['context'] = $config['cluster']['context'] ?? null;
			if (!is_null($config['cluster']['context'])) {
				$parameters[] = $config['cluster']['context'];
			}
		}

		return new \RedisCluster(...$parameters);
	}

	protected static function createRedisSentinel(array $config) {
		$host = '';
		$port = 0;
		foreach ($config['sentinel']['nodes'] ?? [] as $node) {
			[$sentinelHost, $sentinelPort] = explode(':', $node);
			$sentinel = new \RedisSentinel(
				(string)$sentinelHost,
				(int)$sentinelPort,
				$config['sentinel']['timeout'] ?? 0,
				$config['sentinel']['persistent'] ?? null,
				$config['sentinel']['retry_interval'] ?? 0,
				$config['sentinel']['read_timeout'] ?? 0
			);
			$masterInfo = $sentinel->getMasterAddrByName($config['sentinel']['master_name']);
			if (is_array($masterInfo) && count($masterInfo) >= 2) {
				[$host, $port] = $masterInfo;
				break;
			}
		}

		$config['host'] = $host;
		$config['port'] = $port;
		return static::createRedis($config);
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
