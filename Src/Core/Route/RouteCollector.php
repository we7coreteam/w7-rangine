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

use RuntimeException;
use W7\Contract\Router\ValidatorInterface;

class RouteCollector extends \FastRoute\RouteCollector {
	protected array $validators = [];
	protected array $routeNameMap = [];

	public function getCurrentGroupPrefix(): string {
		return $this->currentGroupPrefix;
	}

	public function addRouteByName($name, array $routeData): void {
		$this->routeNameMap[$name] = $routeData;
	}

	public function getRouteByName($name) {
		if (!$routeData = ($this->routeNameMap[$name] ?? [])) {
			throw new RuntimeException('route name ' . $name . ' not exists');
		}

		return $routeData;
	}

	public function registerValidator(ValidatorInterface $validator): void {
		$this->validators[] = $validator;
	}

	public function addRoute($httpMethod, $route, $handler): void {
		if ($this->validate($httpMethod, $route, $handler)) {
			parent::addRoute($httpMethod, $route, $handler);
		}
	}

	protected function validate($httpMethod, $route, $handler): bool {
		/**
		 * @var ValidatorInterface $validator
		 */
		foreach ($this->validators as $validator) {
			if (!$validator->match($httpMethod, $route, $handler)) {
				return false;
			}
		}

		return true;
	}
}
