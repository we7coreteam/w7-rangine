<?php
/**
 * @author donknap
 * @date 18-8-9 下午4:06
 */

namespace W7\Core\Middleware;

use Illuminate\Support\Str;
use W7\App;

class MiddlewareMapping {
	protected $middlewares = [];

	function __construct() {

	}

	public function getMapping() {
		$middlewares = array_merge($this->middlewares, ['last' => $this->getLastMiddle()]);
		return $middlewares;
	}

	public function setMiddleware($handler, $middleware) {
		if (!is_array($middleware)) {
			$middleware = [$middleware];
		}
		foreach ($middleware as $index => $class) {
			if (!is_array($class)) {
				$class = [$class];
			}
			if (!class_exists($class[0])) {
				$class[0] = "W7\\App\\Middleware\\" . Str::studly($class[0]);
			}
			if (!class_exists($class[0])) {
				unset($middleware[$index]);
			}
			$middleware[$index] = $class;
		}

		if (empty($this->middlewares[$handler])) {
			$this->middlewares[$handler] = [];
		}

		$this->middlewares[$handler] = array_merge([], (array) $this->middlewares[$handler], (array) $middleware);
		return true;
	}

	/**
	 * 获取当前启动组件服务中定义的固定last中间件
	 */
	private function getLastMiddle() {
		$class = sprintf("\\W7\\%s\\Middleware\\LastMiddleware", ucfirst(App::$server->type));
		if (class_exists($class)) {
			return [$class];
		} else {
			return [];
		}
	}
}