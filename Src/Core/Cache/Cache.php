<?php
/**
 * @author donknap
 * @date 18-12-30 下午5:38
 */

namespace W7\Core\Cache;


class Cache extends CacheAbstract {

	public function get($key, $default = null) {
		$result = $this->call('get', [$key]);
		if ($result === false || $result === null) {
			return $default;
		}

		return $result;
	}

	public function set($key, $value, $ttl = null) {
		$ttl = $this->getTtl($ttl);
		$params = ($ttl <= 0) ? [$key, $value] : [$key, $value, $ttl];
		return $this->call('set', $params);
	}

	public function delete($key) {
		return (bool)$this->call('del', [$key]);
	}

	public function clear() {
		return $this->call('flushDB', []);
	}

	public function getMultiple($keys, $default = null) {
		$mgetResult = $this->call('mget', [$keys]);
		if ($mgetResult === false) {
			return $default;
		}
		$result = [];
		foreach ($mgetResult ?? [] as $key => $value) {
			$result[$keys[$key]] = $value;
		}

		return $result;
	}

	public function setMultiple($values, $ttl = null) {
		$result = $this->call('mset', [$values]);

		return $result;
	}

	public function deleteMultiple($keys): bool {
		return (bool)$this->call('del', [$keys]);
	}

	public function has($key) {
		return $this->call('exists', [$key]);
	}

	public function __call($method, $arguments) {
		return $this->call($method, $arguments);
	}

	public function call(string $method, array $params) {
		$result = $this->connection->$method(...$params);
		$this->manager->release($this->connection);
		return $result;
	}

	private function getTtl($ttl): int {
		return ($ttl === null) ? 0 : (int)$ttl;
	}
}