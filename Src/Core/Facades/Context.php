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

use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;

/**
 * Class Context
 * @package W7\Core\Facades
 *
 */
class Context extends FacadeAbstract {
	protected static function getFacadeAccessor() {
		return \W7\Core\Helper\Storage\Context::class;
	}

	public static function getContextData() {
		return static::get()->getContextData();
	}

	public static function setContextDataByKey(string $key, $val) {
		static::get()->setContextDataByKey($key, $val);
	}

	public static function getContextDataByKey(string $key, $default = null) {
		return static::get()->getContextDataByKey($key, $default);
	}

	public static function fork($parentCoId) {
		static::get()->fork($parentCoId);
	}

	public static function destroy($coroutineId = null) {
		static::get()->destroy($coroutineId);
	}

	public static function all() {
		return static::get()->all();
	}

	public static function getCoroutineId() {
		return static::get()->getCoroutineId();
	}

	public static function getLastCoId() {
		return static::get()->getLastCoId();
	}

	public static function setRequest(Request $request) {
		static::get()->setRequest($request);
	}

	public static function setResponse(Response $response) {
		static::get()->setResponse($response);
	}

	public static function getResponse() {
		return static::get()->getResponse();
	}

	public static function getRequest() {
		return static::get()->getRequest();
	}

	/**
	 * @return \W7\Core\Helper\Storage\Context
	 */
	private static function get() {
		return parent::resolveFacadeInstance(\W7\Core\Helper\Storage\Context::class);
	}
}
