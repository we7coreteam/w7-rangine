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

namespace W7\Core\Middleware;

use Illuminate\Support\Str;
use W7\App;
use W7\Core\Route\Route;

class MiddlewareMapping {
	/**
	 * 前置的中间件，用于定义一些系统的操作
	 */
	public $beforeMiddleware = [];
	/**
	 * 后置的中间件，用于定义一些系统的操作
	 */
	public $afterMiddleware = [];

	public static function pretreatmentMiddlewares($middlewares) {
		if (!is_array($middlewares) || isset($middlewares['class'])) {
			$middlewares = [$middlewares];
		}
		$pretreatmentMiddlewares = [];
		foreach ($middlewares as $i => $middleware) {
			if (!isset($middleware['class'])) {
				$middleware = self::pretreatmentMiddleware($middleware);
			}
			$pretreatmentMiddlewares[] = $middleware;
		}

		return $pretreatmentMiddlewares;
	}

	public static function pretreatmentMiddleware(string $middleware, array $arguments = []) {
		return [
			'class' => $middleware,
			'arg' => array_values($arguments)
		];
	}

	public function addBeforeMiddleware(string $middleware, $unshift = false) {
		if ($unshift) {
			array_unshift($this->beforeMiddleware, self::pretreatmentMiddleware($middleware));
		} else {
			$this->beforeMiddleware[] = self::pretreatmentMiddleware($middleware);
		}
	}

	public function addAfterMiddleware(string $middleware, $unshift = false) {
		if ($unshift) {
			array_unshift($this->afterMiddleware, self::pretreatmentMiddleware($middleware));
		} else {
			$this->afterMiddleware[] = self::pretreatmentMiddleware($middleware);
		}
	}

	/**
	 * 获取系统最后的中间件
	 */
	protected function getLastMiddleware() {
		return [self::pretreatmentMiddleware(LastMiddleware::class)];
	}

	protected function getControllerMiddleware() {
		if (empty(App::$server->getType())) {
			return [];
		}

		$class = sprintf('\\W7\\%s\\Middleware\\ControllerMiddleware', Str::studly(App::$server->getType()));
		if (class_exists($class)) {
			return [self::pretreatmentMiddleware($class)];
		} else {
			return [self::pretreatmentMiddleware(ControllerMiddleware::class)];
		}
	}

	public function getRouteMiddleWares(Route $route) {
		return array_merge(
			$this->beforeMiddleware,
			$route->getMiddleware(),
			$this->getControllerMiddleware(),
			$this->afterMiddleware,
			$this->getLastMiddleware()
		);
	}
}
