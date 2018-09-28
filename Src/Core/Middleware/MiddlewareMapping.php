<?php
/**
 * @author donknap
 * @date 18-8-9 下午4:06
 */

namespace W7\Core\Middleware;

use W7\App;

class MiddlewareMapping {
	protected $middlewares;
	private $routeConfig;
	private $appConfig;

	function __construct() {
		$this->routeConfig = \iconfig()->getUserConfig("route");
		$this->appConfig = \iconfig()->getUserConfig("app");
	}

	public function getMapping() {
		$middlewares = $this->getByRouteConfig();
		$systemMiddleware = $this->getBySystemConfig();
		$middlewares = array_merge($systemMiddleware['before'], $middlewares, $systemMiddleware['after'], ['last' => $this->getLastMiddle()]);
		return $middlewares;
	}

	/**
	 * 根据路由配置获取中间件
	 */
	private function getByRouteConfig() {
		$middlewares = [];

		foreach ($this->routeConfig as $controller => $route) {
			if ($controller[0] === '/') {
				$path = ucfirst(ltrim($controller, '/')) . "\\";
				$routeConfig = $route;
				foreach ($routeConfig as $controller => $route) {
					$middlewares[$path . ucfirst($controller)] = $this->getByControllerConfig($route);
				}
			} else {
				$middlewares[$controller] = $this->getByControllerConfig($route);
			}
		}
		return $middlewares;
	}

	private function getByControllerConfig($route) {
		$middleware = [];
		if (isset($route['common']) && !empty($route['common'])) {
			$middleware['default'] = $route['common'];
		}
		foreach ($route as $action => $data) {
			if (isset($data['middleware']) && !empty($data['middleware'])) {
				$middleware[$action] = $data['middleware'];
			}
		}
		return $middleware;
	}

	private function getBySystemConfig() {
		$systemMiddlerwares = [
			'before' => !empty($this->appConfig['middleware']['before']) ? $this->appConfig['middleware']['before'] : [],
			'after' => !empty($this->appConfig['middleware']['after']) ? $this->appConfig['middleware']['after'] : [],
		];
		return $systemMiddlerwares;
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

	public function getMiddlewareByRoute(string $routeController, string $routeMethod)
	{
		$result = [];
		$controllerMiddlerwares = !empty($this->middlewares[$routeController])?$this->middlewares[$routeController]:[];
		foreach ($controllerMiddlerwares as $method => $middlerware) {
			if (strstr($method, $routeMethod)|| $method == "default") {
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
	public function setLastMiddleware(string $lasteMiddlerware, array $middlewares)
	{
		array_push($middlewares, $lasteMiddlerware);
		return $middlewares;
	}
}