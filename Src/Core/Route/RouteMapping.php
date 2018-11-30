<?php
/**
 * @author donknap
 * @date 18-8-9 下午3:22
 */

namespace W7\Core\Route;

use FastRoute\RouteCollector;
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

		foreach ($this->routeConfig as $section => $setting) {
			//以/开头的为目录，否则是控制器
			if ($section[0] === '/') {
				$group = $setting;
				foreach ($group as $controller => $setting) {
					if (strpos($controller, '-') !== false) {
						$controller = StringHelper::studly($controller);
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
		print_r($routeCollector->getData());exit;
		return $routeCollector->getData();
	}

	private function formatRouteForFastRoute($routeData, $controller, $group = '') {
		$routes = [];
		foreach ($routeData as $action => $data) {
			if (!isset($data['method']) || empty($data['method'])) {
				continue;
			}
			$query = isset($data['query']) ? $data['query'] : '';

			$method = explode(',', $data['method']);

			$routes[$action]['method'] = $method;
			$routes[$action]['url'] = DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $action . DIRECTORY_SEPARATOR . $query;
			$routes[$action]['handler'] = (!empty($group) ? ucfirst($group) . "\\" : '') . ucfirst($controller) . '-' . $action;

			if (empty($query)) {
				$routes[$action]['url'] = rtrim($routes[$action]['url'], DIRECTORY_SEPARATOR);
			}
		}
		return $routes;
	}
}