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

namespace W7\Core\Facades;

use RuntimeException;
use W7\App;

abstract class FacadeAbstract {
	/**
	 * The resolved object instances.
	 *
	 * @var array
	 */
	protected static $resolvedInstance;

	/**
	 * Resolve the facade root instance from the container.
	 *
	 * @param  object|string  $name
	 * @return mixed
	 */
	protected static function resolveFacadeInstance($name) {
		if (is_object($name)) {
			return $name;
		}

		if (isset(static::$resolvedInstance[$name])) {
			return static::$resolvedInstance[$name];
		}

		return static::$resolvedInstance[$name] = self::getContainer()->get($name);
	}

	/**
	 * Get the root object behind the facade.
	 *
	 * @return mixed
	 */
	public static function getFacadeRoot() {
		return static::resolveFacadeInstance(static::getFacadeAccessor());
	}

	public static function getContainer() {
		return App::getApp()->getContainer();
	}

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	abstract protected static function getFacadeAccessor();

	/**
	 * Handle dynamic, static calls to the object.
	 *
	 * @param  string  $method
	 * @param  array  $args
	 * @return mixed
	 *
	 * @throws \RuntimeException
	 */
	public static function __callStatic($method, $args) {
		$instance = static::getFacadeRoot();

		if (! $instance) {
			throw new RuntimeException('A facade root has not been set.');
		}

		return $instance->$method(...$args);
	}

	public function __call($name, $arguments) {
		$instance = static::getFacadeRoot();

		if (! $instance) {
			throw new RuntimeException('A facade root has not been set.');
		}

		return $instance->$name(...$arguments);
	}
}
