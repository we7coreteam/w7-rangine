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

use W7\App;

class MiddlewareMapping {
	/**
	 * 前置的中间件，用于定义一些系统的操作
	 */
	public $beforeMiddleware = [];
	/**
	 * 后置的中间件，用于定义一些系统的操作
	 */
	public $afterMiddleware = [];

	public function __construct() {
	}

	public function addBeforeMiddleware(string $middleware) {
		$this->beforeMiddleware[] = [$middleware];
	}

	public function addAfterMiddleware(string $middleware) {
		$this->afterMiddleware[] = [$middleware];
	}

	/**
	 * 获取系统最后的中间件
	 */
	private function getLastMiddleware() {
		return [[LastMiddleware::class]];
	}

	private function getControllerMiddleware() {
		if (empty(App::$server->type)) {
			return [];
		}

		$class = sprintf('\\W7\\%s\\Middleware\\ControllerMiddleware', ucfirst(App::$server->type));
		if (class_exists($class)) {
			return [[$class]];
		} else {
			return [[ControllerMiddleware::class]];
		}
	}

	public function getRouteMiddleWares($route) {
		return array_merge(
			$this->beforeMiddleware,
			$route['middleware'],
			$this->getControllerMiddleware(),
			$this->afterMiddleware,
			$this->getLastMiddleware()
		);
	}
}
