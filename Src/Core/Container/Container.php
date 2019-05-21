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
	public function set($name, $handle, $alias = null) {
		if ($this->psrContainer->has($name)) {
			return false;
		}
		$this->container[$name] = $handle;
		if ($alias) {
			$alias = (array)$alias;
			foreach ($alias as $item) {
				$this->container[$item] = $handle;
			}
		}
	}

	public function get($name) {
		if (!$this->psrContainer->has($name)) {
			$this->set($name, function () use ($name) {
				return new $name();
			});
		}

		return $this->psrContainer->get($name);
	}

	public function singleton($name) {
		return $this->get($name);
	}
}