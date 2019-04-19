<?php
/**
 * @author donknap
 * @date 18-8-9 下午3:22
 */

namespace W7\Core\Route;


class RouteMapping {

	private $routeConfig;

	function __construct() {
		$this->routeConfig = \iconfig()->getRouteConfig();
		/**
		 * @todo 增加引入扩展机制的路由
		 */
	}

	/**
	 * @return array|mixed
	 */
	public function getMapping() {
		$routeCollector = irouter();
		$this->initRouteByConfig($routeCollector, $this->routeConfig);
		return $routeCollector->getData();
	}

	private function initRouteByConfig($route, $config, $prefix = '') {
		if (empty($config) || !is_array($config)) {
			return [];
		}

		foreach ($config as $section => $routeItem) {
			//包含prefix时，做为URL的前缀
			if ($section == 'prefix') {
				$prefix .= $routeItem;
				continue;
			}

			if ($section == 'middleware') {

				continue;
			}

			//仅当下属节点不包含prefix时，才会拼接键名
			if (empty($routeItem['prefix'])) {
				$uri = sprintf('%s/%s', $prefix, ltrim($section, '/'));
			} else {
				$uri = sprintf('%s', $prefix);
			}

			if (is_array($routeItem) && empty($routeItem['method']) && empty($routeItem['handler']) && empty($routeItem['uri'])) {
				$this->initRouteByConfig($route, $routeItem, $uri ?? '');
			} else {
				if (!empty($routeItem['uri'])) {
					$uri = $routeItem['uri'];
				}
				if (empty($uri) || empty($routeItem['handler'])) {
					continue;
				}

				if (is_string($routeItem['method'])) {
					$routeItem['method'] = explode(',', $routeItem['method']);
				}

				$route->add(array_map('strtoupper', $routeItem['method']), $uri, $routeItem['handler']);
			}
		}
	}
}