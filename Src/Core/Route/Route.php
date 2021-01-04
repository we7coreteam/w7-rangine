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

use W7\App;
use W7\Core\Helper\StringHelper;

class Route {
	public $name;
	public $module;
	public $handler;
	public $args = [];
	public $middleware = [];
	public $defaults = [];

	public function __construct($name, $module, $handler, array $args = [], array $middleware = [], array $defaults = []) {
		$this->name = $name;
		$this->module = $module;
		$this->handler = $handler;
		$this->args = $args;
		$this->middleware = $middleware;
		$this->defaults = $defaults;
	}

	public function getMiddleware() {
		$middleware = $this->middleware;
		if (!$this->handler instanceof \Closure) {
			list($controller, $method) = $this->handler;
			$classObj = App::getApp()->getContainer()->singleton($controller);
			if (method_exists($classObj, 'getMiddleware')) {
				$controllerMiddleware = collect($classObj->getMiddleware())->reject(function ($data) use ($method) {
					return static::methodExcludedByOptions($method, $data['options']);
				})->pluck('middleware')->all();

				$middleware = array_merge($middleware, $controllerMiddleware);
			}
		}

		return $middleware;
	}

	protected static function methodExcludedByOptions($method, array $options) {
		return (isset($options['only']) && ! in_array($method, (array) $options['only'])) ||
			(! empty($options['except']) && in_array($method, (array) $options['except']));
	}

	public function run() {
		//非闭包函数时实列化对象
		if ($this->handler instanceof \Closure) {
			$controllerHandler = $this->handler;
		} else {
			list($controller, $method) = $this->handler;
			$method = StringHelper::studly($method);
			$classObj = App::getApp()->getContainer()->singleton($controller);
			if (!method_exists($classObj, $method)) {
				throw new \BadMethodCallException("method {$method} not available at class {$controller}");
			}
			$controllerHandler = [$classObj, $method];
		}

		$funArgs = $this->args;
		if (!empty($this->defaults)) {
			$funArgs = array_merge($funArgs, $this->defaults);
		}

		return call_user_func_array($controllerHandler, $funArgs);
	}
}
