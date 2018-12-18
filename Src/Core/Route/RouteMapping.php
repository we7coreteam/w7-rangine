<?php
/**
 * @author donknap
 * @date 18-8-9 下午3:22
 */

namespace W7\Core\Route;

use FastRoute\RouteCollector;
use Monolog\Handler\IFTTTHandler;
use W7\Core\Helper\StringHelper;

class RouteMapping {

	private $routeConfig;

	public function ab() {

	}

	function __construct() {
		$this->routeConfig = \iconfig()->getUserConfig("route");
	}

	/**
	 * @return array|mixed
	 */
	public function getMapping() {
		if (empty($this->routeConfig)) {
			return [];
		}
		$routeCollector = new RouteCollector(new \FastRoute\RouteParser\Std(), new \FastRoute\DataGenerator\GroupCountBased());
		unset($this->routeConfig['@middleware']);

		foreach ($this->routeConfig as $section => $setting) {
			//以/开头的为目录，否则是控制器
			if ($section[0] === '/') {
				$commonMethod = '';
				$group = $setting;
				foreach ($group as $controller => $setting) {
					if ($controller == '@method') {
						$commonMethod = $setting;
					}
					if ($controller[0] === '@') {
						continue;
					}
					if (empty($setting['@method']) && !empty($commonMethod)) {
						$setting['@method'] = $commonMethod;
					}
					$controllerRoute = $this->formatRouteForFastRoute($setting, $controller, ltrim($section, '/'));
					if (!empty($controllerRoute)) {
						$routeCollector->addGroup($section, function (RouteCollector $route) use ($controllerRoute) {
							foreach ($controllerRoute as $action => $info) {
								$route->addRoute($info['method'], $info['url'], $info['handler']);
							}
						});
					}
				}
			} elseif ($section[0] === '@') {
				continue;
			} else {
				$controller = $section;
				$controllerRoute = $this->formatRouteForFastRoute($setting, $controller);
				if (!empty($controllerRoute)) {
					foreach ($controllerRoute as $action => $info) {
						$routeCollector->addRoute($info['method'], $info['url'], $info['handler']);
					}
				}
			}
		}
		return $routeCollector->getData();
	}

	private function formatRouteForFastRoute($routeData, $controller, $group = '') {
		$routes = [];
		$commonMethod = $routeData['@method'] ?? Route::METHOD_BOTH_GP;
		$commonQuery = $routeData['@query'] ?? '';

		foreach ($routeData as $action => $data) {
			//跳过公共配置
			if ($action[0] == '@') {
				continue;
			}

			$method = !empty($data['@method']) ? $data['@method'] : $commonMethod;
			$query = $data['@query'] ?? $commonQuery;

			$method = explode(',', $method);
			//清除掉Method两边的空格
			if (!empty($method)) {
				foreach ($method as &$value) {
					$value = trim($value);
				}
				unset($value);
			}

			$routes[$action]['method'] = $method;
			if (!empty($data['rewrite'])) {
				$routes[$action]['url'] =  DIRECTORY_SEPARATOR . $data['@rewrite'];
			} else {
				$routes[$action]['url'] =  DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $action . $query;
			}

			$routes[$action]['handler'] = (!empty($group) ? ucfirst($group) . "\\" : '') . ucfirst($controller) . '@' . $action;

			if (empty($query)) {
				$routes[$action]['url'] = rtrim($routes[$action]['url'], DIRECTORY_SEPARATOR);
			}
		}
		return $routes;
	}
}