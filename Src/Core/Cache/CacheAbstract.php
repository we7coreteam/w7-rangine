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
	protected $cacheName;
	protected $config;
	/**
	 * @var ConnectorManager
	 */
	protected $connectionResolver;

	public function __construct($name, array $config = []) {
		$this->cacheName = $name;
		$config['name'] = $name;
		$this->config = $config;
	}

	public function setConnectionResolver(ConnectorManager $connectorManager) {
		$this->connectionResolver = $connectorManager;
	}

	protected function getConnection() {
		return $this->connectionResolver->connect($this->config);
	}
}
