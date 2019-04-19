<?php
/**
 * @author donknap
 * @date 18-8-9 下午3:22
 */

namespace W7\Core\Route;


use W7\Core\Middleware\MiddlewareMapping;

class RouteMapping {

	private $routeConfig;

	/**
	 * @var MiddlewareMapping
	 */
	private $middlewareMapping;

	function __construct() {
		$this->middlewareMapping = iloader()->singleton(MiddlewareMapping::class);
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

	private function initRouteByConfig($route, $config, $prefix = '', $middleware = []) {
		if (empty($config) || !is_array($config)) {
			return [];
		}

		foreach ($config as $section => $routeItem) {
			//包含prefix时，做为URL的前缀
			if ($section == 'prefix') {
				$prefix .= $routeItem;
				continue;
			}

			//仅当下属节点不包含prefix时，才会拼接键名
			if (empty($routeItem['prefix'])) {
				$uri = sprintf('%s/%s', $prefix, ltrim($section, '/'));
			} else {
				$uri = sprintf('%s', $prefix);
			}

			if ($section == 'middleware') {
				$middleware = array_merge([], $middleware, (array) $routeItem);
			}

			if (is_array($routeItem) && empty($routeItem['method']) && empty($routeItem['handler']) && empty($routeItem['uri'])) {
				$this->initRouteByConfig($route, $routeItem, $uri ?? '', $middleware);
			} else {
				if (!is_array($routeItem) || empty($routeItem['handler'])) {
					continue;
				}

				if (!empty($routeItem['uri'])) {
					$uri = $routeItem['uri'];
				}
				if (empty($uri) || empty($routeItem['handler'])) {
					continue;
				}

				if (is_string($routeItem['method'])) {
					$routeItem['method'] = explode(',', $routeItem['method']);
				}

				//组合中间件
				if (empty($routeItem['middleware'])) {
					$routeItem['middleware'] = [];
				}
				$routeItem['middleware'] = array_merge([], $middleware, (array) $routeItem['middleware']);

				$route->middleware($routeItem['middleware'])->add(array_map('strtoupper', $routeItem['method']), $uri, $routeItem['handler']);
			}
		}
	}
}