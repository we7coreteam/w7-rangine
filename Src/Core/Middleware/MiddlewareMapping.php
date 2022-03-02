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
use W7\Core\Route\Route;

class MiddlewareMapping {
	public $beforeMiddleware = [];
	public $afterMiddleware = [];

	public static function pretreatmentMiddlewares($middlewares) {
		if (!is_array($middlewares) || isset($middlewares['class'])) {
			$middlewares = [$middlewares];
		}
		$pretreatmentMiddlewares = [];
		foreach ($middlewares as $middleware) {
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

	protected function getLastMiddleware() {
		return [self::pretreatmentMiddleware(LastMiddleware::class)];
	}

	protected function getControllerMiddleware($serverType) {
		$class = sprintf('\\W7\\%s\\Middleware\\ControllerMiddleware', Str::studly($serverType));
		if (class_exists($class)) {
			return [self::pretreatmentMiddleware($class)];
		}

		return [self::pretreatmentMiddleware(ControllerMiddleware::class)];
	}

	public function getRouteMiddleWares(Route $route, $serverType) {
		return array_merge(
			$this->beforeMiddleware,
			$route->getMiddleware(),
			$this->getControllerMiddleware($serverType),
			$this->afterMiddleware,
			$this->getLastMiddleware()
		);
	}
}
