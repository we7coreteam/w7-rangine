<?php

/**
 * WeEngine Api System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Core\Route;

use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;
use W7\App;
use W7\Contract\Router\RouteInterface;
use W7\Core\Middleware\MiddlewareMapping;

class Route implements RouteInterface {
	public $name;
	public $uri;
	public $module;
	public $handler;
	public $args = [];
	public $middleware = [];
	public $defaults = [];
	public $option = [];

	public function __construct($name, $uri, $module, $handler, array $args = [], array $middleware = [], array $defaults = [], $options = []) {
		$this->name = $name;
		$this->uri = $uri;
		$this->module = $module;
		$this->handler = $handler;
		$this->args = $args;
		$this->middleware = $middleware;
		$this->defaults = $defaults;
		$this->option = $options;
	}

	public function getName() {
		return $this->name;
	}

	public function getUri() {
		return $this->uri;
	}

	public function getModule() {
		return $this->module;
	}

	public function getController() {
		if ($this->handler instanceof \Closure) {
			return $this->handler;
		}

		return $this->handler[0];
	}

	public function getAction() {
		if ($this->handler instanceof \Closure) {
			return '';
		}

		return $this->handler[1];
	}

	public function getArgs(): array {
		return $this->args;
	}

	public function getDefaults(): array {
		return $this->defaults;
	}

	public function getMiddleware() : array {
		$middleware = $this->middleware;
		if (!$this->handler instanceof \Closure) {
			list($controller, $method) = $this->handler;
			$classObj = App::getApp()->getContainer()->get($controller);
			if (method_exists($classObj, 'getMiddleware')) {
				$controllerMiddleware = collect($classObj->getMiddleware())->reject(function ($data) use ($method) {
					return static::methodExcludedByOptions($method, $data['options']);
				})->pluck('middleware')->all();

				$middleware = array_merge($middleware, MiddlewareMapping::pretreatmentMiddlewares($controllerMiddleware));
			}
		}

		return $middleware;
	}

	public function getOption() : array {
		return $this->option;
	}

	protected static function methodExcludedByOptions($method, array $options) {
		return (isset($options['only']) && ! in_array($method, (array) $options['only'])) ||
			(! empty($options['except']) && in_array($method, (array) $options['except']));
	}

	public function run(RequestInterface $request) {
		//非闭包函数时实列化对象
		if ($this->handler instanceof \Closure) {
			$controllerHandler = $this->handler;
		} else {
			list($controller, $method) = $this->handler;
			$method = Str::studly($method);
			$classObj = App::getApp()->getContainer()->get($controller);
			if (!method_exists($classObj, $method)) {
				throw new \BadMethodCallException("method {$method} not available at class {$controller}");
			}
			$controllerHandler = [$classObj, $method];
		}

		array_unshift($this->args, $request);
		$funArgs = $this->args;
		if (!empty($this->defaults)) {
			$funArgs = array_merge($funArgs, $this->defaults);
		}

		return call_user_func_array($controllerHandler, $funArgs);
	}
}
