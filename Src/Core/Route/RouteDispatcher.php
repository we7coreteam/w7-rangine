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
use W7\Contract\Router\RouterInterface;
use W7\Core\Helper\FileLoader;
use W7\Core\Server\ServerEnum;

class RouteDispatcher extends GroupCountBased {
	protected static $routeDefinitionMap = [];

	public static function getRouteCacheFileName() {
		return 'route.php';
	}

	public static function getRouteDefinetions(string $routeMapping, $routeCacheGroup = ServerEnum::TYPE_HTTP) {
		if (isset(static::$routeDefinitionMap[$routeCacheGroup])) {
			return static::$routeDefinitionMap[$routeCacheGroup];
		}

		if (App::getApp()->routeIsCached()) {
			$routeCacheFile = App::getApp()->getRouteCachePath() . $routeCacheGroup . '.' . self::getRouteCacheFileName();
			$routeDefinitions = require $routeCacheFile;
			if (!is_array($routeDefinitions)) {
				throw new \RuntimeException('Invalid cache file "' . $routeCacheFile . '"');
			}
		} else {
			$container = App::getApp()->getContainer();
			/**
			 * @var RouteMapping $routeMapping
			 */
			$basePath = App::getApp()->getBasePath();
			$fileLoader = new FileLoader($basePath, App::getApp()->getConfigger()->get('app.setting.file_ignore', []));
			$routeMapping = new $routeMapping($container->get(RouterInterface::class), $fileLoader);
			$routeDefinitions = $routeMapping->getMapping($basePath . '/route');
		}
		static::$routeDefinitionMap[$routeCacheGroup] = $routeDefinitions;

		return $routeDefinitions;
	}

	public static function getDispatcherWithRouteMapping(string $routeMapping, $routeCacheGroup = ServerEnum::TYPE_HTTP) {
		$routeDefinitions = static::getRouteDefinetions($routeMapping, $routeCacheGroup);

		return new static($routeDefinitions);
	}
}
