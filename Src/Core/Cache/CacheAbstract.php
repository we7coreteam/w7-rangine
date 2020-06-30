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
	/**
	 * @var ConnectorManager
	 */
	protected static $connectionResolver;
	protected $channelName;

	public function __construct($name = 'default') {
		$this->channelName = $name;
	}

	public static function setConnectionResolver(ConnectorManager $connectorManager) {
		static::$connectionResolver = $connectorManager;
	}

	protected function getConnection() {
		return static::$connectionResolver->connect($this->channelName);
	}
}
