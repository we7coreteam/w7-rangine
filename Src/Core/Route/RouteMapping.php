<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Route;

use W7\Core\Middleware\MiddlewareMapping;

class RouteMapping {
	private $routeConfig;
	private $routeKeyWords = ['prefix', 'method', 'middleware', 'name', 'namespace', 'uri', 'handler'];

	/**
	 * @var MiddlewareMapping
	 */
	private $middlewareMapping;

	public function __construct() {
		$this->middlewareMapping = iloader()->get(MiddlewareMapping::class);
		$this->routeConfig = \iconfig()->getRouteConfig();
		/**
		 * @todo 增加引入扩展机制的路由
		 */
	}

	public function setRouteConfig($routeConfig) {
		$this->routeConfig = $routeConfig;
	}

	public function getRouteConfig() {
		return $this->routeConfig;
	}

	/**
	 * @return array|mixed
	 */
	public function getMapping() {
		if (!empty($this->routeConfig)) {
			foreach ($this->routeConfig as $index => $routeConfig) {
				$this->initRouteByConfig($routeConfig);
			}
		}
		$this->registerSystemRoute();

		return irouter()->getData();
	}

	private function initRouteByConfig($config) {
		$prefix = '';
		$middleware = [];
		$name = '';
		$routeNamespace = '';

		$prefix .= '/' . trim($config['prefix'] ?? '', '/');

		$method = $config['method'] ?? [];
		if (!empty($config['middleware'])) {
			$middleware = array_merge([], $middleware, (array) $config['middleware']);
		}
		if (!empty($config['name'])) {
			$name .= $config['name'] . '.';
		}
		if (!empty($config['namespace'])) {
			$routeNamespace = $config['namespace'];
		}

		foreach ($this->routeKeyWords as $routeKeyWord) {
			if (isset($config[$routeKeyWord])) {
				unset($config[$routeKeyWord]);
			}
		}

		foreach ($config as $section => $routeItem) {
			//包含prefix时，做为URL的前缀
			$this->parseRoute($section, $routeItem, $prefix, $middleware, $method, $name, $routeNamespace);
		}
	}

	private function parseRoute($key, $route, $prefix = '', $middleware = [], $method = '', $name = '', $routeNamespace = '') {
		$childRoutes = array_diff(array_keys($route), $this->routeKeyWords);
		if (!empty($childRoutes)) {
			$prefix .= '/' . trim($route['prefix'] ?? '', '/');
			$method = $route['method'] ?? [];

			if (!empty($route['middleware'])) {
				$middleware = array_merge([], $middleware, (array) $route['middleware']);
			}

			if (!empty($route['name'])) {
				$name .= $route['name'] . '.';
			}

			if (!empty($route['namespace'])) {
				$routeNamespace = $route['namespace'];
			}
			foreach ($childRoutes as $section => $childRoute) {
				$this->parseRoute($key . '/' . $childRoute, $route[$childRoute], $prefix, $middleware, $method, $name, $routeNamespace);
			}
		} else {
			//如果没有指定Uri,则根据数组结构生成uri
			if (empty($route['uri'])) {
				if (!empty($route['prefix'])) {
					$prefix .= '/' . trim($route['prefix'] ?? '', '/');
				}

				$prefixArr = explode('/', $prefix);
				$tmpKey = explode('/', $key);
				foreach ($tmpKey as $index => $value) {
					if (!empty($prefixArr[$index + 2])) {
						$tmpKey[$index] = $prefixArr[$index + 2];
					}
				}
				if (!empty($prefixArr[1])) {
					array_unshift($tmpKey, $prefixArr[1]);
				}
				$tmpKey = implode('/', $tmpKey);
				$uri = sprintf('/%s', $tmpKey);
				$route['uri'] = $uri;
			}
			if (empty($route['uri'])) {
				return false;
			}

			//如果没有指定handler，则按数组层级生成命名空间+Controller@当前键名
			if (empty($route['handler'])) {
				$namespace = explode('/', ltrim($key, '/'));
				$namespace = array_slice($namespace, 0, -1);

				$namespace = array_map('ucfirst', $namespace);
				$key = explode('/', $key);
				$key = end($key);
				$route['handler'] = sprintf('%sController@%s', implode('\\', $namespace), $key);
			}

			if (empty($route['method'])) {
				$route['method'] = $method;
			}

			if (empty($route['method'])) {
				$route['method'] = Route::METHOD_BOTH_GP;
			}

			if (is_string($route['method'])) {
				$route['method'] = explode(',', $route['method']);
			}

			if (!isset($route['name'])) {
				$route['name'] = '';
			}
			if (empty($route['name']) && !($route['handler'] instanceof \Closure)) {
				$route['name'] = $name . ltrim(strrchr($route['handler'], '@'), '@');
			}

			//组合中间件
			if (empty($route['middleware'])) {
				$route['middleware'] = [];
			}
			$route['middleware'] = array_unique(array_merge([], $middleware, (array) $route['middleware']));

			irouter()->group([
				'namespace' => $routeNamespace
			], function () use ($route) {
				irouter()->middleware($route['middleware'])->add(array_map('strtoupper', $route['method']), $route['uri'], $route['handler'], $route['name']);
			});
		}
	}

	//如果用户自定义了系统路由，则按照用户的路由走
	public function registerSystemRoute() {
		try {
			irouter()->get('/favicon.ico', function () {
				return icontext()->getResponse()->withContent('');
			});
		} catch (\Throwable $e) {
			null;
		}
	}
}
