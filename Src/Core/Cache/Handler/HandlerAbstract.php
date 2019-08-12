<?php

namespace W7\Core\Cache\Handler;

use Psr\SimpleCache\CacheInterface;

abstract class HandlerAbstract implements CacheInterface{
	abstract public static function getHandler($config) : HandlerAbstract;

	public function alive() {
		return true;
	}
}