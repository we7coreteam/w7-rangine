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
use W7\App;
use W7\Core\Facades\Container;
use W7\Core\Helper\FileLoader;
use W7\Core\Server\ServerEnum;
use W7\Core\Facades\Router;

class RouteDispatcher extends GroupCountBased {
	public static $routeCacheFileName = 'route.php';

	public static function getDispatcherWithRouteMapping(string $routeMapping, $routeCacheGroup = ServerEnum::TYPE_HTTP) {
		$routeCacheFile = App::getApp()->getRouteCachePath() . $routeCacheGroup . '.' . self::$routeCacheFileName;
		if (file_exists($routeCacheFile)) {
			$routeDefinitions = require $routeCacheFile;
			if (!is_array($routeDefinitions)) {
				throw new \RuntimeException('Invalid cache file "' . $routeCacheFile . '"');
			}
		} else {
			/**
			 * @var RouteMapping $routeMapping
			 */
			$routeMapping = new $routeMapping(Router::getFacadeRoot(), Container::get(FileLoader::class));
			$routeDefinitions = $routeMapping->getMapping();
		}

		return new static($routeDefinitions);
	}
}
