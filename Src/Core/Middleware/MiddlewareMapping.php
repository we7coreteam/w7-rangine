<?php
/**
 * @author donknap
 * @date 18-8-9 下午4:06
 */

namespace W7\Core\Middleware;

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
		return [[ControllerMiddleware::class]];
	}
}