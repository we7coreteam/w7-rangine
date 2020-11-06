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

namespace W7\Core\Facades;

use W7\App;

/**
 * Class Container
 * @package W7\Core\Facades
 *
 * @method static void registerDeferredService(array $services)
 * @method static void registerDeferredServiceLoader(\Closure $loader)
 * @method static void set($name, $handle, ...$params)
 * @method static bool has($name)
 * @method static mixed get($name, array $params = [])
 * @method static void append($dataKey, array $value, $default = [])
 * @method static mixed clone($name, array $params = [])
 * @method static void delete($name)
 * @method static mixed clear()
 *
 * @see \W7\Core\Container\Container
 */
class Container extends FacadeAbstract {
	protected static function getFacadeAccessor() {
		return '';
	}

	public static function getFacadeRoot() {
		return self::getContainer();
	}

	public static function singleton($name, array $params = []) {
		return App::getApp()->getContainer()->singleton($name, $params);
	}
}
