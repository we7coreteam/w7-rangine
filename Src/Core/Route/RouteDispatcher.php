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

namespace W7\Core\Route;

use FastRoute\Dispatcher\GroupCountBased;
use W7\Core\Server\ServerEnum;

class RouteDispatcher extends GroupCountBased {
	protected static $routeCacheFileName = 'route.cache';

	public static function getCachedRoutePath() {
		return BASE_PATH . '/bootstrap/cache/';
	}

	public static function getDispatcherWithRouteMapping(RouteMapping $routeMapping, $routeCacheGroup = ServerEnum::TYPE_HTTP) {
		$routeCacheFile = self::getCachedRoutePath() . $routeCacheGroup . '.' . self::$routeCacheFileName;
		if (file_exists($routeCacheFile)) {
			$routeDefinitions = require $routeCacheFile;
			if (!is_array($routeDefinitions)) {
				throw new \RuntimeException('Invalid cache file "' . $routeCacheFile . '"');
			}
		} else {
			$routeDefinitions = $routeMapping->getMapping();
		}

		return new static($routeDefinitions);
	}
}
