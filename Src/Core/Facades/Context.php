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
 * Class Context
 * @package W7\Core\Facades
 *
 * @method static \W7\Http\Message\Server\Request getRequest()
 * @method static \W7\Http\Message\Server\Response getResponse()
 * @method static array getContextData()
 * @method static void setRequest(\W7\Http\Message\Server\Request $request)
 * @method static void setResponse(\W7\Http\Message\Server\Response $response)
 * @method static void setContextDataByKey(string $key, $val)
 * @method static mixed getContextDataByKey(string $key, $default = null)
 * @method static void fork($parentCoId)
 * @method static void destroy($coroutineId = null)
 * @method static array all()
 * @method static integer getCoroutineId()
 * @method static integer getLastCoId()
 */
class Context extends FacadeAbstract {
	protected static function getFacadeAccessor() {
		return \W7\Core\Helper\Storage\Context::class;
	}
}
