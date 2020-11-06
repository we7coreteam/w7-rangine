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

use FastRoute\RouteParser\Std;
use FastRoute\DataGenerator\GroupCountBased;
use W7\Contract\Router\RouterInterface;
use W7\Contract\Router\ValidatorInterface;

class Router implements RouterInterface {
	const METHOD_POST = 'POST';
	const METHOD_GET = 'GET';
	const METHOD_BOTH_GP = 'POST,GET';
	const METHOD_PUT = 'PUT';
	const METHOD_PATCH = 'PATCH';
	const METHOD_DELETE = 'DELETE';
	const METHOD_HEAD = 'HEAD';
	const METHOD_OPTIONS = 'OPTIONS';
	const METHOD_ALL = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

	//保存document_root这些配置
	protected $config = [];

	private $routerCollector;

	private $groupStack = [];

	/**
	 * 当前路由中间件
	 * @var array
	 */
	private $currentMiddleware = [];
	private $defaultNamespace = 'W7\App';
	private $defaultModule = 'system';

	private $name = '';

	public function __construct(RouteCollector $routeCollector = null, $config = []) {
		if (!$routeCollector) {
			$routeCollector = new RouteCollector(new Std(), new GroupCountBased());
		}
		$this->routerCollector = $routeCollector;
		$this->config = $config;
	}

	public function getRouterCollector() {
		return $this->routerCollector;
	}

	public function setRouterCollector(RouteCollector $routeCollector) {
		$this->routerCollector = $routeCollector;
	}

	public function registerValidator(ValidatorInterface $validator) {
		$this->routerCollector->registerValidator($validator);
	}

	private function parseGroupOption($option) {
		if (!is_array($option)) {
			$prefix = $option;
			$option = [
				'prefix' => $prefix
			];
		}
		return [
			'prefix' => $option['prefix'] ?? '',
			'namespace' => $option['namespace'] ?? '',
			'module' => $option['module'] ?? ''
		];
	}

	/**
	 * middleware按照分组隔开，子分组的middleware始终含有父的middleware
	 * @param $prefix
	 * @param callable $callback
	 * @return bool
	 */
	public function group($option, callable $callback) {
		$option = $this->parseGroupOption($option);

		$this->routerCollector->addGroup($option['prefix'], function (RouteCollector $routeCollector) use ($callback, $option) {
			$groupInfo = [];
			$groupInfo['prefix'] = $option['prefix'];
			$groupInfo['namespace'] = $option['namespace'];

			$groupInfo['middleware'] = $this->currentMiddleware;
			$this->currentMiddleware = [];

			$groupInfo['name'] = $this->name;
			$this->name = '';

			$groupInfo['module'] = $option['module'];

			$this->groupStack[] = $groupInfo;
			$callback($this);
			array_pop($this->groupStack);
		});
		return true;
	}

	/**
	 * 注册一个允许所有协议的路由
	 * @param $uri
	 * @param $handler
	 * @param string $name
	 * @param array $defaults
	 * @return bool
	 * @throws \ErrorException
	 */
	public function any($uri, $handler, $name = '', $defaults = []) {
		return $this->add(self::METHOD_ALL, $uri, $handler, $name, $defaults);
	}

	public function all($uri, $handler) {
		$this->any($uri, $handler);
	}

	/**
	 * 注册一个Post 路由
	 */
	public function post($uri, $handler) {
		$result = $this->add(self::METHOD_POST, $uri, $handler);
		return $result;
	}

	public function get($uri, $handler) {
		$result = $this->add(self::METHOD_GET, $uri, $handler);
		return $result;
	}

	public function put($uri, $handler) {
		$result = $this->add(self::METHOD_PUT, $uri, $handler);
		return $result;
	}

	public function delete($uri, $handler) {
		$result = $this->add(self::METHOD_DELETE, $uri, $handler);
		return $result;
	}

	public function patch($uri, $handler) {
		$result = $this->add(self::METHOD_PATCH, $uri, $handler);
		return $result;
	}

	public function head($uri, $handler) {
		$result = $this->add(self::METHOD_HEAD, $uri, $handler);
		return $result;
	}

	public function options($uri, $handler) {
		$result = $this->add(self::METHOD_OPTIONS, $uri, $handler);
		return $result;
	}

	private function isStaticResource($resource) {
		if (is_string($resource)) {
			$documentRoot = $this->config['document_root'] ?? '';
			$enableStatic = $this->config['enable_static_handler'] ?? false;
			if ($enableStatic && $documentRoot) {
				$module = $this->getModule() === $this->defaultModule ? '' : DIRECTORY_SEPARATOR . $this->getModule();
				$path = $documentRoot . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . ltrim($resource, DIRECTORY_SEPARATOR);
				return file_exists($path);
			}
		}

		return false;
	}

	private function getStaticResourcePath($destination) {
		$module = $this->getModule();
		$destination = ltrim($destination, DIRECTORY_SEPARATOR);
		//如果是通过provider注册的，自动补充前缀
		if ($module !== $this->defaultModule) {
			$destination = $module . DIRECTORY_SEPARATOR . $destination;
		}
		$destination = DIRECTORY_SEPARATOR . $destination;
		return $destination;
	}

	/**
	 * 注册一个直接跳转路由
	 * @param $uri
	 * @param $destination
	 * @param int $status
	 * @throws \ErrorException
	 */
	public function redirect($uri, $destination, $status = 302) {
		if ($this->isStaticResource($destination)) {
			$destination = $this->getStaticResourcePath($destination);
		}

		$this->any($uri, ['\W7\Core\Controller\RedirectController', 'index'], '', [$destination, $status]);
	}

	/**
	 * 注册一个直接显示的静态页
	 * @param $uri
	 * @param string $view
	 * @throws \ErrorException
	 */
	public function view($uri, string $view) {
		$this->add([self::METHOD_GET, self::METHOD_HEAD], $uri, $view);
	}

	/**
	 * 注册一个支持多种协议的路由
	 * @param $methods
	 * @param $uri
	 * @param $handler
	 * @param string $name
	 * @param array $defaults
	 * @return bool
	 * @throws \ErrorException
	 */
	public function add($methods, $uri, $handler, $name = '', $defaults = []) {
		if ($this->isStaticResource($handler)) {
			$defaults = [$this->config['document_root'] . $this->getStaticResourcePath($handler)];
			$handler = ['\W7\Core\Controller\StaticResourceController', 'index'];
		}
		$handler = $this->checkHandler($handler);

		if (empty($methods)) {
			$methods = self::METHOD_BOTH_GP;
		}
		if (!is_array($methods)) {
			$methods = [$methods];
		}
		//清除掉Method两边的空格
		foreach ($methods as &$value) {
			$value = strtoupper(trim($value));
		}
		unset($value);

		$routeHandler = [
			'handler' => $handler,
			'module' => $this->getModule(),
			'controller_namespace' => $this->getNamespace() . '\Controller\\',
			'middleware_namespace' => $this->getNamespace() . '\Middleware\\',
			'middleware' => [
				'before' => [],
				'after' => []
			],
			'uri' => $this->routerCollector->getCurrentGroupPrefix() . $uri,
			'defaults' => (array)$defaults
		];

		if (!$name) {
			$name = $this->name;
		}
		if (!$name && !($handler instanceof \Closure)) {
			$name = trim(implode('.', array_filter(array_column($this->groupStack, 'name'))) . '.' . $handler[1], '.');
		}
		$routeHandler['name'] = $name;
		$this->name = '';

		//处理namespace
		if (!($routeHandler['handler'] instanceof \Closure)) {
			$routeHandler['handler'][0] = $this->prependGroupNamespace($routeHandler['controller_namespace'], $routeHandler['handler'][0]);
		}

		//先获取上级的middleware
		//添加完本次路由后，要清空掉当前Middleware值，以便下次使用
		$groupMiddleware = [];
		$middleWares = array_filter(array_column($this->groupStack, 'middleware'));
		array_walk($middleWares, function ($value) use (&$groupMiddleware) {
			$groupMiddleware = array_merge($groupMiddleware, $this->checkMiddleware($value));
		});
		$routeHandler['middleware']['before'] = array_merge($groupMiddleware, $routeHandler['middleware']['before'], $this->checkMiddleware($this->currentMiddleware));
		$this->currentMiddleware = [];

		try {
			$this->routerCollector->addRoute($methods, $uri, $routeHandler);
		} catch (\Throwable $e) {
			$dispatcher = new RouteDispatcher($this->getData());
			foreach ($methods as $method) {
				$route = $dispatcher->dispatch($method, $routeHandler['uri']);
				if (!empty($route[1]['module'])) {
					throw new \RuntimeException('route "' . $routeHandler['uri'] . '" for method "' . $method . '" exists in ' . $route[1]['module']);
				}
			}
		}

		return true;
	}

	public function resource($name, $controller, $options = []) {
		return new ResourceRoute(new ResourceRegister($this), $name, $controller, $options);
	}

	public function apiResource($name, $controller, $options = []) {
		return new ResourceRoute(new ResourceRegister($this), $name, $controller, $options);
	}

	protected function prependGroupNamespace($namespace, $class) {
		return (strpos($class, $namespace) === false && strpos($class, '\\') !== 0)
			? $namespace . $class : $class;
	}

	public function middleware($name) {
		if (!is_array($name)) {
			$name = func_get_args();
			$name = [$name];
		}
		foreach ($name as $i => $row) {
			if (!is_array($row)) {
				$row = [$row];
			}
			$this->currentMiddleware[] = $row;
		}

		return $this;
	}

	/**
	 * 指定该路由的名字，用于验权之类的操作
	 * @param $name
	 * @return $this
	 */
	public function name($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * 获取路由列表
	 * @return array
	 */
	public function getData() {
		return $this->routerCollector->getData();
	}

	private function checkHandler($handler) {
		if ($handler instanceof \Closure) {
			return $handler;
		}
		if (is_string($handler)) {
			$handler = explode('@', $handler, 2);
		}
		if (count($handler) != 2) {
			throw new \RuntimeException('route handler ' . $handler[0] . ' error');
		}
		list($className, $action) = $handler;
		if (empty($action)) {
			$action = 'index';
		}

		return [
			$className,
			$action,
		];
	}

	private function checkMiddleware($middleware) {
		if (!is_array($middleware)) {
			$middleware = [$middleware];
		}
		foreach ($middleware as $index => $class) {
			if (!is_array($class)) {
				$class = [$class];
			}

			$namespace = $this->getNamespace() . '\Middleware\\';
			$class[0] = $this->prependGroupNamespace($namespace, $class[0]);
			$middleware[$index] = $class;
		}
		return $middleware;
	}

	private function getNamespace() {
		$groupNamespace = implode('\\', array_filter(array_column($this->groupStack, 'namespace')));
		return empty($groupNamespace) ? $this->defaultNamespace : $groupNamespace;
	}

	private function getModule() {
		$module = array_filter(array_column($this->groupStack, 'module'));
		return empty($module) ? $this->defaultModule : end($module);
	}
}
