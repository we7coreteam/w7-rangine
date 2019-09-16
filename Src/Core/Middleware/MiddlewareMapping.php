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

namespace W7\Core\Middleware;

use W7\App;

class MiddlewareMapping {
	public function __construct() {
	}

	/**
	 * 获取系统最后的中间件
	 */
	public function getLastMiddleware() {
		return [[LastMiddleware::class]];
	}

	public function getControllerMiddleware() {
		if (empty(App::$server->type)) {
			return [];
		}
		$class = sprintf('\\W7\\%s\\Middleware\\ControllerMiddleware', ucfirst(App::$server->type));
		if (class_exists($class)) {
			return [[$class]];
		} else {
			return [[ControllerMiddleware::class]];
		}
	}
}
