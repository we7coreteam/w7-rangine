<?php
/**
 * @author donknap
 * @date 18-8-9 下午4:06
 */

namespace W7\Core\Middleware;

use W7\App;

class MiddlewareMapping {
	function __construct() {
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
		$class = sprintf("\\W7\\%s\\Middleware\\ControllerMiddleware", ucfirst(App::$server->type));
		if (class_exists($class)) {
			return [[$class]];
		} else {
			return [[ControllerMiddleware::class]];
		}
	}
}