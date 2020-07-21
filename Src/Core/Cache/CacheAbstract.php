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
use W7\Core\Facades\Context;

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
		$name = $this->getContextKey($this->cacheName);
		$connection = Context::getContextDataByKey($name);

		if (! $connection instanceof CacheInterface) {
			try {
				$connection = $this->connectionResolver->connect($this->config);
				Context::setContextDataByKey($name, $connection);
			} finally {
				if ($connection && isCo()) {
					defer(function () use ($connection, $name) {
						$this->connectionResolver->release($connection);
						Context::setContextDataByKey($name, null);
					});
				}
			}
		}

		return $connection;
	}

	private function getContextKey($name): string {
		return sprintf('cache.connection.%s', $name);
	}
}
