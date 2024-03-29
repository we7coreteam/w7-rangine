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

namespace W7\Core\Cache;

class Cache extends CacheAbstract {
	public function set($key, $value, $ttl = null) {
		$value = $this->handler->pack($value);
		return $this->handler->set($this->warpKey($key), $value, $ttl);
	}

	public function get($key, $default = null) {
		$result = $this->handler->get($this->warpKey($key), $default);
		if ($result === false || $result === null) {
			return $default;
		}

		return $this->handler->unpack($result);
	}

	public function delete($key) {
		return (bool)$this->handler->delete($this->warpKey($key));
	}

	public function setMultiple($values, $ttl = null) {
		$values = (array)$values;
		$cacheValues = [];
		foreach ($values as $key => $value) {
			$cacheValues[$this->warpKey($key)] = $this->handler->pack($value);
		}
		return $this->handler->setMultiple($cacheValues, $ttl);
	}

	public function getMultiple($keys, $default = null) {
		$keys = (array)$keys;
		$cacheKeys = [];
		foreach ($keys as $key) {
			$cacheKeys[] = $this->warpKey($key);
		}
		$mgetResult = $this->handler->getMultiple($cacheKeys, $default);
		if ($mgetResult === false) {
			return $default;
		}
		$result = [];
		foreach ($mgetResult ?? [] as $key => $value) {
			$result[$keys[$key]] = $this->handler->unpack($value);
		}

		return $result;
	}

	public function deleteMultiple($keys): bool {
		$keys = (array)$keys;
		foreach ($keys as &$key) {
			$key = $this->warpKey($key);
		}
		return (bool)$this->handler->deleteMultiple($keys);
	}

	public function has($key) {
		return (bool)$this->handler->has($this->warpKey($key));
	}

	public function clear() {
		return (bool)$this->handler->clear();
	}

	public function __call($method, $arguments) {
		return $this->handler->$method(...$arguments);
	}
}
