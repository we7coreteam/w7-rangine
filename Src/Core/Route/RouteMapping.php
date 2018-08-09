<?php
/**
 * @author donknap
 * @date 18-8-9 下午3:22
 */

namespace W7\Core\Route;

use w7\HttpRoute\HttpServer;

class RouteMapping {

	private $routeConfig;

	function __construct() {
		$this->routeConfig = \iconfig()->getUserConfig("route");
	}

	/**
	 * @return array|mixed
	 */
	public function getMapping()
	{
		$routes = ['POST' => [], 'GET' => []];
		if (empty($this->routeConfig)) {
			return [];
		}

		foreach ($this->routeConfig as $controller => $setting) {
			$controllerRoute = $this->formatRouteForFastRoute($setting, $controller);

			$routes['POST'] = array_merge($routes['POST'], $controllerRoute['POST']);
			$routes['GET'] = array_merge($routes['GET'], $controllerRoute['GET']);
		}
		//组装到fastroute中
		$routeList = [];
		$fastRoute = new HttpServer();
		foreach ($routes as $httpMethod => $routeData) {
			$routeList = array_merge_recursive($routeList, $fastRoute->addRoute($httpMethod, $routeData));
		}
		return $routeList;
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