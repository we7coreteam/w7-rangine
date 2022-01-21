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

use Psr\SimpleCache\CacheInterface;

abstract class CacheAbstract implements CacheInterface {
	protected $name;
	protected $cacheOptions = [];
	/**
	 * @var StorageResolver
	 */
	protected $storageResolver;

	public function __construct($name, $cacheOptions = []) {
		$this->name = $name;
		$this->cacheOptions = $cacheOptions;
	}

	public function getName() {
		return $this->name;
	}

	public function setStorageResolver(StorageResolver $connectorManager) {
		$this->storageResolver = $connectorManager;
	}

	protected function getStorage() {
		return $this->storageResolver->storage($this->name);
	}

	protected function warpKey($key) {
		return ($this->cacheOptions['prefix'] ?? '') . $key;
	}
}
