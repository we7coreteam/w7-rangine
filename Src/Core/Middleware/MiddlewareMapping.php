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
	}

	public function getMapping() {
		$middlewares = $this->getByRouteConfig();
		$middlewares = array_merge($middlewares, ['last' => $this->getLastMiddle()]);
		return $middlewares;
	}

	public function setMiddleware() {

	}

	/**
	 * 根据路由配置获取中间件
	 */
	private function getByRouteConfig() {
		$middlewares = [];
		//全局中间件
		$commonMiddleware = [
			'before' => $this->routeConfig['@middleware']['before'] ?? [],
			'after' => $this->routeConfig['@middleware']['after'] ?? [],
		];

		unset($this->routeConfig['@middleware']);

		foreach ($this->routeConfig as $controller => $route) {
			$controllerCommonMiddleware = [];

			if ($controller[0] === '/') {
				$path = ucfirst(ltrim($controller, '/')) . "\\";
				$routeConfig = $route;
				foreach ($routeConfig as $controller => $route) {
					if ($controller === '@middleware') {
						$controllerCommonMiddleware = array_merge($commonMiddleware['before'], (array)$route, $commonMiddleware['after']);
						continue;
					} elseif ($controller[0] == '@') {
						continue;
					}
					$middlewares[$path . ucfirst($controller)] = $this->getByControllerConfig($route, $controllerCommonMiddleware);
				}
			} else {
				if ($controller === '@middleware') {
					$controllerCommonMiddleware = array_merge($commonMiddleware['before'], (array)$route, $commonMiddleware['after']);
					continue;
				}
				$middlewares[ucfirst($controller)] = $this->getByControllerConfig($route, $controllerCommonMiddleware);
			}
		}
		return $middlewares;
	}

	private function getByControllerConfig($route, $commonMiddleware = []) {
		if (!empty($commonMiddleware)) {
			$commonMiddleware = array_unique($commonMiddleware);
		}
		$controllerCommonMiddleware = $route['@middleware'] ?? [];

		$middleware = [];
		foreach ($route as $action => $data) {
			//跳过公共配置
			if ($action[0] == '@') {
				continue;
			}
			$data['@middleware'] = $data['@middleware'] ?? $controllerCommonMiddleware;
			$middleware[$action] = array_merge($commonMiddleware, (array) $data['@middleware'], $this->getLastMiddle());
		}
		return $middleware;
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