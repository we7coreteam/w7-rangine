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

class CacheManager {
	protected $caches = [];
	protected $channelsConfig;
	protected $defaultChannel;
	/**
	 * @var ConnectorManager
	 */
	protected $connectorResolver;

	public function __construct($channelsConfig = [], $defaultChannel = 'default') {
		$this->channelsConfig = $channelsConfig;
		$this->defaultChannel = $defaultChannel;
	}

	public function setConnectorResolver($connectorResolver) {
		$this->connectorResolver = $connectorResolver;
	}

	public function channel($name = 'default') : CacheInterface {
		return $this->getCache($name);
	}

	protected function getCache($channel) {
		if (empty($this->caches[$channel]) && !empty($this->channelsConfig[$channel])) {
			$this->registerCache($channel, $this->channelsConfig[$channel]);
		}
		if (empty($this->caches[$channel])) {
			$channel = $this->defaultChannel;
		}

		if (!empty($this->caches[$channel]) && $this->caches[$channel] instanceof CacheInterface) {
			return $this->caches[$channel];
		}

		throw new \RuntimeException('cache channel ' . $channel . ' not support');
	}

	public function registerCache($channel, array $config) {
		$cache = new Cache($channel, $config);
		$cache->setConnectionResolver($this->connectorResolver);

		return $cache;
	}

	public function __call($name, $arguments) {
		return $this->channel()->$name(...$arguments);
	}
}
