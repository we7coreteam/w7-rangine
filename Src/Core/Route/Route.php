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

use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;
use W7\App;
use W7\Contract\Router\RouteInterface;
use W7\Core\Helper\Traiter\MethodDependencyResolverTrait;
use W7\Core\Middleware\MiddlewareMapping;

class Route implements RouteInterface {
	use MethodDependencyResolverTrait;

	public string $name;
	public string $uri;
	public string $module;
	public mixed $handler;
	public array $args = [];
	public array $middleware = [];
	public array $defaults = [];

	public function __construct($name, $uri, $module, $handler, array $args = [], array $middleware = [], array $defaults = []) {
		$this->name = $name;
		$this->uri = $uri;
		$this->module = $module;
		$this->handler = $handler;
		$this->args = $args;
		$this->middleware = $middleware;
		$this->defaults = $defaults;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getUri(): string {
		return $this->uri;
	}

	public function getModule(): string {
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
			[$controller, $method] = $this->handler;
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

	protected static function methodExcludedByOptions($method, array $options): bool {
		return (isset($options['only']) && !in_array($method, (array)$options['only'], true)) ||
			(! empty($options['except']) && in_array($method, (array)$options['except'], true));
	}

	/**
	 * @throws \ReflectionException
	 */
	public function run(RequestInterface $request) {
		if ($this->handler instanceof \Closure) {
			return $this->runCallable();
		}
		return $this->runController();
	}

	protected function runController() {
		[$controller, $method] = $this->handler;
		$method = Str::studly($method);
		$classObj = App::getApp()->getContainer()->get($controller);
		if (!method_exists($classObj, $method)) {
			throw new \BadMethodCallException("method {$method} not available at class {$controller}");
		}

		$funArgs = $this->args;
		if (!empty($this->defaults)) {
			$funArgs = array_merge($funArgs, $this->defaults);
		}
		$funArgs = $this->resolveClassMethodDependencies($funArgs, $classObj, $method);

		$controllerHandler = [$classObj, $method];
		return call_user_func_array($controllerHandler, $funArgs);
	}

	/**
	 * @throws \ReflectionException
	 */
	protected function runCallable() {
		$callable = $this->handler;
		$funArgs = $this->args;
		if (!empty($this->defaults)) {
			$funArgs = array_merge($funArgs, $this->defaults);
		}
		$funArgs = $this->resolveMethodDependencies($funArgs, new \ReflectionFunction($callable));

		return call_user_func_array($callable, $funArgs);
	}
}
