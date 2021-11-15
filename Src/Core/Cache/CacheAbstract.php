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
use W7\Core\Cache\Handler\HandlerAbstract;

abstract class CacheAbstract implements CacheInterface {
	protected string $name;
	protected array $cacheOptions = [];
	protected ConnectionResolver $connectionResolver;

	public function __construct(string $name, array $cacheOptions = []) {
		$this->name = $name;
		$this->cacheOptions = $cacheOptions;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setConnectionResolver(ConnectionResolver $connectorManager): void {
		$this->connectionResolver = $connectorManager;
	}

	protected function getConnection() {
		return $this->connectionResolver->connection($this->name);
	}

	/**
	 * @throws \Throwable
	 */
	protected function tryAgainIfCausedByLostConnection(\Throwable $e, \Closure $callback, HandlerAbstract $connection, callable $tryCall) {
		if ($connection->isCausedByLostConnection($e)) {
			$this->connectionResolver->reconnect($this->getName());
			return $tryCall($callback);
		}

		throw $e;
	}

	protected function warpKey($key): string {
		return ($this->cacheOptions['prefix'] ?? '') . $key;
	}
}
