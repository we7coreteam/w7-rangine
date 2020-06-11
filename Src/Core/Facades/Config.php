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

/**
 * Class Config
 * @package W7\Core\Facades
 *
 * @method static string getBuiltInConfigPath()
 * @method static mixed get($key, $default = null)
 * @method static array set($key, $value)
 *
 * @see \W7\Core\Config\Config
 */
class Config extends FacadeAbstract {
	protected static function getFacadeAccessor() {
		return \W7\Core\Config\Config::class;
	}
}
