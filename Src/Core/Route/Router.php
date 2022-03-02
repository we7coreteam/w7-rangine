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
use W7\Core\Middleware\MiddlewareMapping;

class Router implements RouterInterface {
	const METHOD_POST = 'POST';
	const METHOD_GET = 'GET';
	const METHOD_BOTH_GP = 'POST,GET';
	const METHOD_PUT = 'PUT';
	const METHOD_PATCH = 'PATCH';
	const METHOD_DELETE = 'DELETE';
	const METHOD_HEAD = 'HEAD';
	const METHOD_MQTT_TOPIC = 'MQTT_TOPIC';
	const METHOD_OPTIONS = 'OPTIONS';
	const METHOD_ALL = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'MQTT_TOPIC'];

	protected $config = [];

	private $routerCollector;

	private $groupStack = [];

	private $currentMiddleware = [];
	private $appNamespace;
	private $defaultModule = 'system';

	private $name = '';

	public function __construct(RouteCollector $routeCollector = null, $config = []) {
		if (!$routeCollector) {
			$routeCollector = new RouteCollector(new Std(), new GroupCountBased());
		}
		$this->appNamespace = $config['app_namespace'] ?? '';
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

	public function any($uri, $handler, $name = '', $defaults = []) {
		return $this->add(self::METHOD_ALL, $uri, $handler, $name, $defaults);
	}

	public function all($uri, $handler) {
		$this->any($uri, $handler);
	}

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

	public function topic($uri, $handler) {
		$result = $this->add(self::METHOD_MQTT_TOPIC, $uri, $handler);
		return $result;
	}

	public function options($uri, $handler) {
		return $this->add(self::METHOD_OPTIONS, $uri, $handler);
	}

	private function isStaticResource($resource) {
		if (is_string($resource)) {
			$documentRoot = $this->config['document_root'] ?? '';
			$enableStatic = $this->config['enable_static_handler'] ?? false;
			if ($enableStatic && $documentRoot) {
				$module = $this->getModule() === $this->defaultModule ? '' : ('/' . $this->getModule());
				$path = $documentRoot . $module . '/' . ltrim($resource, '/');
				return file_exists($path);
			}
		}

		return false;
	}

	private function getStaticResourcePath($destination) {
		$module = $this->getModule();
		$destination = ltrim($destination, '/');
		if ($module !== $this->defaultModule) {
			$destination = $module . '/' . $destination;
		}
		return '/' . $destination;
	}

	public function redirect($uri, $destination, $status = 302) {
		if ($this->isStaticResource($destination)) {
			$destination = $this->getStaticResourcePath($destination);
		}

		$this->any($uri, ['\W7\Core\Controller\RedirectController', 'index'], '', [$destination, $status]);
	}

	public function view($uri, string $view) {
		$this->add([self::METHOD_GET, self::METHOD_HEAD], $uri, $view);
	}

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
		foreach ($methods as &$value) {
			$value = strtoupper(trim($value));
		}
		unset($value);

		$routeHandler = [
			'handler' => $handler,
			'module' => $this->getModule(),
			'controller_namespace' => $this->getNamespace() . '\Controller\\',
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
		$routeHandler['name'] = $name;
		$this->name = '';

		if (!($routeHandler['handler'] instanceof \Closure)) {
			$routeHandler['handler'][0] = $this->prependGroupNamespace($routeHandler['controller_namespace'], $routeHandler['handler'][0]);
		}

		$groupMiddleware = [];
		$middleWares = array_filter(array_column($this->groupStack, 'middleware'));
		array_walk($middleWares, function ($value) use (&$groupMiddleware) {
			$groupMiddleware = array_merge($groupMiddleware, $value);
		});
		$routeHandler['middleware']['before'] = array_merge($groupMiddleware, $routeHandler['middleware']['before'], $this->currentMiddleware);
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
			throw $e;
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
		$this->currentMiddleware = MiddlewareMapping::pretreatmentMiddlewares($name);

		return $this;
	}

	public function name($name) {
		$this->name = $name;
		return $this;
	}

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
		[$className, $action] = $handler;
		if (empty($action)) {
			$action = 'index';
		}

		return [
			$className,
			$action,
		];
	}

	private function getNamespace() {
		$groupNamespace = implode('\\', array_filter(array_column($this->groupStack, 'namespace')));
		return empty($groupNamespace) ? $this->appNamespace : $groupNamespace;
	}

	private function getModule() {
		$module = array_filter(array_column($this->groupStack, 'module'));
		return empty($module) ? $this->defaultModule : end($module);
	}
}
