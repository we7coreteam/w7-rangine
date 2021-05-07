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

use W7\Contract\Router\RouterInterface;

class ResourceRegister {
	protected $router;
	protected $parameters = [];
	protected $resourceDefaults = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

	public function __construct(RouterInterface $router) {
		$this->router = $router;
	}

	public function register($name, $controller, $options = []) {
		if (!empty($options['parameters'])) {
			$this->parameters = $options['parameters'];
		}

		if (strpos($name, '/') !== false) {
			$this->prefixedResource($name, $controller, $options);
			return $this;
		}

		$base = $this->getResourceWildcard(last(explode('.', $name)));
		foreach ($this->getResourceActions($options) as $action) {
			$this->{'addResource' . ucfirst($action)}($name, $base, $controller, $options);
		}
	}

	/**
	 * Get the applicable resource methods.
	 *
	 * @param  array  $defaults
	 * @param  array  $options
	 * @return array
	 */
	protected function getResourceActions($options) {
		$methods = $this->resourceDefaults;

		if (isset($options['only'])) {
			$methods = array_intersect($methods, (array) $options['only']);
		}

		if (isset($options['except'])) {
			$methods = array_diff($methods, (array) $options['except']);
		}

		return $methods;
	}

	/**
	 * Replace parameter names
	 * @param  string  $value
	 * @return string
	 */
	protected function getResourceWildcard($value) {
		if (isset($this->parameters[$value])) {
			$value = $this->parameters[$value];
		}

		return str_replace('-', '_', $value);
	}

	/**
	 * Multiple parameters are not currently supported, which means the name format is app.module.test
	 * @param $name
	 * @return string
	 */
	protected function getResourceUri($name) {
		if (!$name) {
			return '';
		}
		return '/' . $name;
	}

	protected function getResourceHandler($controller, $action, $options) {
		$name = null;
		if (isset($options['names'])) {
			if (is_string($options['names'])) {
				$name = $options['names'];
			} elseif (isset($options['names'][$action])) {
				$name = $options['names'][$action];
			}
		}
		$this->router->name($name);

		if (!empty($options['middleware'])) {
			$this->router->middleware($options['middleware']);
		}

		return $controller . '@' . $action;
	}

	protected function getResourcePrefix($name) {
		$segments = explode('/', $name);
		$prefix = implode('/', array_slice($segments, 0, -1));
		return [end($segments), $prefix];
	}

	/**
	 * If /app/module/test is entered, the parent group is /app/module
	 * @param $name
	 * @param $controller
	 * @param $options
	 * @return bool
	 */
	protected function prefixedResource($name, $controller, $options) {
		[$name, $prefix] = $this->getResourcePrefix($name);

		$callback = function ($router) use ($name, $controller, $options) {
			/**
			 * @var RouterInterface $router
			 */
			$router->resource($name, $controller, $options);
		};

		return $this->router->group($prefix, $callback);
	}

	protected function addResourceIndex($name, $base, $controller, $options) {
		$uri = $this->getResourceUri($name);
		$handler = $this->getResourceHandler($controller, 'index', $options);

		$this->router->get($uri, $handler);
	}

	protected function addResourceCreate($name, $base, $controller, $options) {
		$uri = $this->getResourceUri($name) . '/create';
		$handler = $this->getResourceHandler($controller, 'create', $options);

		$this->router->get($uri, $handler);
	}

	protected function addResourceStore($name, $base, $controller, $options) {
		$uri = $this->getResourceUri($name);
		$handler = $this->getResourceHandler($controller, 'store', $options);

		$this->router->post($uri, $handler);
	}

	protected function addResourceShow($name, $base, $controller, $options) {
		$uri = $this->getResourceUri($name).'/{'.$base.'}';
		$handler = $this->getResourceHandler($controller, 'show', $options);

		$this->router->get($uri, $handler);
	}

	protected function addResourceEdit($name, $base, $controller, $options) {
		$uri = $this->getResourceUri($name).'/{'.$base.'}/edit';
		$handler = $this->getResourceHandler($controller, 'edit', $options);

		$this->router->get($uri, $handler);
	}

	protected function addResourceUpdate($name, $base, $controller, $options) {
		$uri = $this->getResourceUri($name).'/{'.$base.'}';
		$handler = $this->getResourceHandler($controller, 'update', $options);

		$this->router->put($uri, $handler);
		$this->router->patch($uri, $handler);
	}

	protected function addResourceDestroy($name, $base, $controller, $options) {
		$uri = $this->getResourceUri($name).'/{'.$base.'}';
		$handler = $this->getResourceHandler($controller, 'destroy', $options);

		$this->router->delete($uri, $handler);
	}
}
