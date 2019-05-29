<?php

namespace W7\Core\Container;

use Pimple\Container as PimpleContainer;
use Pimple\Psr11\Container as PsrContainer;

class Container {
	private $container;
	private $psrContainer;

	public function __construct() {
		$this->container = new PimpleContainer();
		$this->psrContainer = new PsrContainer($this->container);
	}

	/**
	 * @param $name
	 * @param $value
	 * @param null $alias
	 * @return bool
	 */
	public function set($name, $handle) {
		if ($this->has($name)) {
			return false;
		}
		$this->container[$name] = $handle;
	}

	public function get($name) {
		if (!$this->has($name)) {
			$this->set($name, function () use ($name) {
				return new $name();
			});
		}

		return $this->psrContainer->get($name);
	}
	
	public function has($name) {
		return $this->psrContainer->has($name);
	}
}