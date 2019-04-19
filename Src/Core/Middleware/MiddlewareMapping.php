<?php
/**
 * @author donknap
 * @date 18-8-9 下午4:06
 */

namespace W7\Core\Middleware;

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

	public function getMiddlewareByRoute(string $routeController, string $routeMethod) {
		$result = [];
		$controllerMiddlerwares = !empty($this->middlewares[$routeController]) ? $this->middlewares[$routeController] : [];
		foreach ($controllerMiddlerwares as $method => $middlerware) {
			if (strstr($method, $routeMethod) || $method == "default") {
				$result = array_merge($result, $controllerMiddlerwares[$method]);
			}
		}
		return $result;
	}

	/**
	 * @param string $middlerware
	 * @param array $dispather
	 * @return array
	 */
	public function setLastMiddleware(string $lasteMiddlerware, array $middlewares) {
		array_push($middlewares, $lasteMiddlerware);
		return $middlewares;
	}
}