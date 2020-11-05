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

use W7\Contract\Cache\CacheFactoryInterface;
use Psr\SimpleCache\CacheInterface;

class CacheFactory implements CacheFactoryInterface {
	protected $cacheMap = [];
	protected $defaultChannel;
	/**
	 * @var ConnectionResolver
	 */
	protected $connectionResolver;

	public function __construct($defaultChannel = 'default') {
		$this->defaultChannel = $defaultChannel;
	}

	public function setConnectionResolver($connectionResolver) {
		$this->connectionResolver = $connectionResolver;
	}

	public function registerCache(CacheAbstract $cache) {
		$this->cacheMap[$cache->getName()] = $cache;
	}

	public function channel($name = 'default') : CacheInterface {
		return $this->getCache($name);
	}

	protected function getCache($channel) {
		if (empty($this->cacheMap[$channel])) {
			$cache = new Cache($channel);
			$cache->setConnectionResolver($this->connectionResolver);
			$this->cacheMap[$channel] = $cache;
		}

		return $this->cacheMap[$channel];
	}

	public function __call($name, $arguments) {
		return $this->channel()->$name(...$arguments);
	}
}
