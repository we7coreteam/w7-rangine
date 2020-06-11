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
use W7\Core\Facades\Container;

abstract class CacheAbstract implements CacheInterface {
	/**
	 * @var ConnectorManager
	 */
	protected $connectorManager;
	protected $channelName = 'default';

	public function setChannelName(string $channelName) {
		$this->channelName = $channelName;
	}

	/**
	 * 选择一个缓存通道
	 * @param $name
	 * @return $this
	 */
	public function channel($name) {
		if (empty($name)) {
			throw new \RuntimeException('Invalid cache channel name');
		}
		if (!Container::has('cache-' . $name)) {
			throw new \RuntimeException('cache not support the channel');
		}
		return Container::get('cache-' . $name);
	}

	public function setConnectorManager(ConnectorManager $connectorManager) {
		$this->connectorManager = $connectorManager;
	}

	protected function getConnection() {
		return $this->connectorManager->connect($this->channelName);
	}
}
