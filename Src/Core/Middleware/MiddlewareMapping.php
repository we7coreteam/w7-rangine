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
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use W7\App;
use W7\Core\Route\Route;

class MiddlewareMapping {
	public array $beforeMiddleware = [];
	public array $afterMiddleware = [];

	public static function pretreatmentMiddlewares($middlewares): array {
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

	public static function pretreatmentMiddleware(string $middleware, array $arguments = []): array {
		return [
			'class' => $middleware,
			'arg' => array_values($arguments)
		];
	}

	public function addBeforeMiddleware(string $middleware, $unshift = false): void {
		if ($unshift) {
			array_unshift($this->beforeMiddleware, self::pretreatmentMiddleware($middleware));
		} else {
			$this->beforeMiddleware[] = self::pretreatmentMiddleware($middleware);
		}
	}

	public function addAfterMiddleware(string $middleware, $unshift = false): void {
		if ($unshift) {
			array_unshift($this->afterMiddleware, self::pretreatmentMiddleware($middleware));
		} else {
			$this->afterMiddleware[] = self::pretreatmentMiddleware($middleware);
		}
	}

	protected function getLastMiddleware(): array {
		return [self::pretreatmentMiddleware(LastMiddleware::class)];
	}

	protected function getControllerMiddleware(): array {
		if (empty(App::$server->getType())) {
			return [];
		}

		$class = sprintf('\\W7\\%s\\Middleware\\ControllerMiddleware', Str::studly(App::$server->getType()));
		if (class_exists($class)) {
			return [self::pretreatmentMiddleware($class)];
		}

		return [self::pretreatmentMiddleware(ControllerMiddleware::class)];
	}

	public function getRouteMiddleWares(Route $route): array {
		return array_merge(
			$this->beforeMiddleware,
			$route->getMiddleware(),
			$this->getControllerMiddleware(),
			$this->afterMiddleware,
			$this->getLastMiddleware()
		);
	}
}
