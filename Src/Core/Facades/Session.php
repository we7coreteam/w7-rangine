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
 * Class Session
 * @package W7\Core\Facades
 *
 * @method static void setId($sessionId)
 * @method static string getId()
 * @method static bool set($key, $value)
 * @method static bool has($key)
 * @method static mixed get($key, $default = '')
 * @method static mixed delete(array|string $keys)
 * @method static array all()
 * @method static bool destroy()
 * @method static bool close()
 * @method static void gc()
 *
 * @see \W7\Core\Session\Session
 */
class Session extends FacadeAbstract {
	protected static function getFacadeAccessor() {
		return '';
	}

	public static function getFacadeRoot() {
		return Context::getRequest()->session;
	}
}
