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

namespace W7\Core\Helper\Traiter;

use Illuminate\Support\Arr;
use Illuminate\Support\Reflector;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Trait MethodDependencyResolverTrait
 * @package W7\Core\Helper\Traiter
 *
 * @see \Illuminate\Routing\RouteDependencyResolverTrait
 */
trait MethodDependencyResolverTrait {
	use AppCommonTrait;

	public function resolveClassMethodDependencies(array $parameters, $instance, $method): array {
		if (! method_exists($instance, $method)) {
			return $parameters;
		}

		return $this->resolveMethodDependencies(
			$parameters,
			new ReflectionMethod($instance, $method)
		);
	}

	public function resolveMethodDependencies(array $parameters, ReflectionFunctionAbstract $reflector): array {
		$instanceCount = 0;

		$values = array_values($parameters);

		$skippableValue = new \stdClass;

		foreach ($reflector->getParameters() as $key => $parameter) {
			$instance = $this->transformDependency($parameter, $parameters, $skippableValue);

			if ($instance !== $skippableValue) {
				$instanceCount++;

				$this->spliceIntoParameters($parameters, $key, $instance);
			} elseif (! isset($values[$key - $instanceCount]) &&
				$parameter->isDefaultValueAvailable()) {
				$this->spliceIntoParameters($parameters, $key, $parameter->getDefaultValue());
			}
		}

		return $parameters;
	}

	protected function transformDependency(ReflectionParameter $parameter, $parameters, $skippableValue) {
		$className = Reflector::getParameterClassName($parameter);

		// If the parameter has a type-hinted class, we will check to see if it is already in
		// the list of parameters. If it is we will just skip it as it is probably a model
		// binding and we do not want to mess with those; otherwise, we resolve it here.
		if ($className && ! $this->alreadyInParameters($className, $parameters)) {
			return $parameter->isDefaultValueAvailable() ? null : $this->getContainer()->make($className);
		}

		return $skippableValue;
	}

	protected function alreadyInParameters($class, array $parameters): bool {
		return ! is_null(Arr::first($parameters, function ($value) use ($class) {
			return $value instanceof $class;
		}));
	}

	protected function spliceIntoParameters(array &$parameters, $offset, $value): void {
		array_splice(
			$parameters,
			$offset,
			0,
			[$value]
		);
	}
}
