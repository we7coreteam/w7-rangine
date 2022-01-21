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

use W7\Core\Cache\Handler\HandlerAbstract;

class Cache extends CacheAbstract {
	public function set($key, $value, $ttl = null) {
		return $this->call(function (HandlerAbstract $handler) use ($key, $value, $ttl) {
			$value = $handler->pack($value);
			return $handler->set($this->warpKey($key), $value, $ttl);
		});
	}

	public function get($key, $default = null) {
		return $this->call(function (HandlerAbstract $handler) use ($key, $default) {
			$result = $handler->get($this->warpKey($key), $default);
			if ($result === false || $result === null) {
				return $default;
			}

			return $handler->unpack($result);
		});
	}

	public function delete($key) {
		return (bool)$this->call(function (HandlerAbstract $handler) use ($key) {
			return (bool)$handler->delete($this->warpKey($key));
		});
	}

	public function setMultiple($values, $ttl = null) {
		return $this->call(function (HandlerAbstract $handler) use ($values, $ttl) {
			$values = (array)$values;
			$cacheValues = [];
			foreach ($values as $key => $value) {
				$cacheValues[$this->warpKey($key)] = $handler->pack($value);
			}
			return $handler->setMultiple($cacheValues, $ttl);
		});
	}

	public function getMultiple($keys, $default = null) {
		return $this->call(function (HandlerAbstract $handler) use ($keys, $default) {
			$keys = (array)$keys;
			$cacheKeys = [];
			foreach ($keys as $key) {
				$cacheKeys[] = $this->warpKey($key);
			}
			$mgetResult = $handler->getMultiple($cacheKeys, $default);
			if ($mgetResult === false) {
				return $default;
			}
			$result = [];
			foreach ($mgetResult ?? [] as $key => $value) {
				$result[$keys[$key]] = $handler->unpack($value);
			}

			return $result;
		});
	}

	public function deleteMultiple($keys): bool {
		return (bool)$this->call(function (HandlerAbstract $handler) use ($keys) {
			$keys = (array)$keys;
			foreach ($keys as &$key) {
				$key = $this->warpKey($key);
			}
			return (bool)$handler->deleteMultiple($keys);
		});
	}

	public function has($key) {
		return (bool)$this->call(function (HandlerAbstract $handler) use ($key) {
			return (bool)$handler->has($this->warpKey($key));
		});
	}

	public function clear() {
		return (bool)$this->call(function (HandlerAbstract $handler) {
			return (bool)$handler->clear();
		});
	}

	public function __call($method, $arguments) {
		return $this->call(function (HandlerAbstract $handler) use ($method, $arguments) {
			return $handler->$method(...$arguments);
		});
	}

	public function call(\Closure $method) {
		$connection = $this->getStorage();

		return $method($connection);
	}
}
