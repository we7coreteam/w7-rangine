<?php
/**
 * @author donknap
 * @date 18-8-9 下午3:22
 */

namespace W7\Core\Route;

use FastRoute\RouteCollector;

class RouteMapping {

	private $routeConfig;

	function __construct() {
		$this->routeConfig = \iconfig()->getUserConfig("route");
	}

	/**
	 * @return array|mixed
	 */
	public function getMapping() {
		$routes = ['POST' => [], 'GET' => []];
		if (empty($this->routeConfig)) {
			return [];
		}

		foreach ($this->routeConfig as $controller => $setting) {
			$controllerRoute = $this->formatRouteForFastRoute($setting, $controller);

			$routes['POST'] = array_merge($routes['POST'], $controllerRoute['POST']);
			$routes['GET'] = array_merge($routes['GET'], $controllerRoute['GET']);
		}

		$routeCollector = new RouteCollector(new \FastRoute\RouteParser\Std(), new \FastRoute\DataGenerator\GroupCountBased());

		//组装到fastroute中
		$routeList = [];
		foreach ($routes as $httpMethod => $routeData) {
			if (is_array($routeData)) {
				foreach ($routeData as $hander => $url) {
					$routeCollector->addRoute($httpMethod, $url, $hander);
				}
			}
		}

		return $routeCollector->getData();
	}

	private function formatRouteForFastRoute($routeData, $controller) {
		$routes = [];
		foreach ($routeData as $action => $data) {
			if (!isset($data['method']) || empty($data['method'])) {
				continue;
			}
			$query = isset($data['query']) ? $data['query'] : '';

			$method = explode(',', $data['method']);
			foreach ($method as $methodRow) {
				$routes[$methodRow][$controller. '-' . $action] = DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $action . DIRECTORY_SEPARATOR . $query;
			}
		}
		return $routes;
	}
}