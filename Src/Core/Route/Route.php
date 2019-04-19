<?php
/**
 * @author donknap
 * @date 18-12-17 下午8:18
 */

namespace W7\Core\Route;


use FastRoute\RouteCollector;

class Route {
	const METHOD_POST = 'POST';
	const METHOD_GET = 'GET';
	const METHOD_BOTH_GP = 'POST,GET';
	const METHOD_PUT = 'PUT';
	const METHOD_PATCH = 'PATCH';
	const METHOD_DELETE = 'DELETE';
	const METHOD_HEAD = 'head';
	const METHOD_ALL = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

	private $router;

	public function __construct() {
		$this->router = new RouteCollector(new \FastRoute\RouteParser\Std(), new \FastRoute\DataGenerator\GroupCountBased());
	}


	public function group($prefix, callable $callback) {
		return $this->router->addGroup($prefix, function (RouteCollector $route) use ($callback) {
			$callback($this);
		});
	}


	/**
	 * 注册一个允许所有协议的路由
	 * @param $route
	 * @param $handler
	 */
	public function any($uri, $handler) {
		return $this->add(self::METHOD_ALL, $uri, $handler);
	}

	public function all($uri, $handler) {
		$this->any($uri, $handler);
	}

	/**
	 * 注册一个Post 路由
	 */
	public function post($uri, $handler) {
		return $this->add(self::METHOD_POST, $uri, $handler);
	}

	public function get($uri, $handler) {
		return $this->add(self::METHOD_GET, $uri, $handler);
	}

	public function put($uri, $handler) {
		return $this->add(self::METHOD_PUT, $uri, $handler);
	}

	public function delete($uri, $handler) {
		return $this->add(self::METHOD_DELETE, $uri, $handler);
	}

	public function patch($uri, $handler) {
		return $this->add(self::METHOD_PATCH, $uri, $handler);
	}

	public function head($uri, $handler) {
		return $this->add(self::METHOD_HEAD, $uri, $handler);
	}


	/**
	 * 注册一个支持多种协议的路由
	 * @param $methods
	 * @param $uri
	 * @param $handler
	 */
	public function add($methods, $uri, $handler) {
		$handler = $this->checkHandler($handler);

		if (empty($methods)) {
			$methods = SELF::METHOD_ALL;
		}

		if (!is_array($methods)) {
			$methods = [$methods];
		}

		//清除掉Method两边的空格
		foreach ($methods as &$value) {
			$value = trim($value);
		}
		unset($value);

		return $this->router->addRoute(array_map('strtoupper', (array) $methods), $uri, $handler);
	}

	/**
	 * 注册一个直接跳转路由
	 * @param $uri
	 * @param $destination
	 * @param int $status
	 */
	public function redirect($uri, $destination, $status = 301) {
		throw new \InvalidArgumentException('还未实现');
	}

	/**
	 * 注册一个直接显示的静态页
	 * @param $uri
	 * @param $view
	 * @param array $data
	 */
	public function view($uri, $view, $data = []) {
		throw new \InvalidArgumentException('还未实现');
	}

	public function resource() {
		throw new \InvalidArgumentException('还未实现');
	}

	public function middleware($name) {
		return $this;
	}

	/**
	 * 获取路由列表
	 * @return array
	 */
	public function getData() {
		return $this->router->getData();
	}

	private function checkHandler($handler) {
		$error = $handler;

		if ($handler instanceof \Closure) {
			return true;
		}

		if (is_string($handler)) {
			$handler = explode('@', $handler);
		}

		list($className, $action) = $handler;

		if (empty($action)) {
			$action = 'index';
		}

		if (!class_exists($className)) {
			$className = "\\W7\\App\\Controller\\{$className}";
		}

		if (!class_exists($className)) {
			throw new \InvalidArgumentException('Invalid ' . $error);
		}

		return [
			$className,
			$action,
		];
	}
}