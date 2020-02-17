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

namespace W7\Core\Container;

use Pimple\Container as PimpleContainer;
use Pimple\Psr11\Container as PsrContainer;

/**
 * ＠@mixin PimpleContainer
 */
class Container {
	private $container;
	private $psrContainer;

	public function __construct() {
		$this->container = new PimpleContainer();
		$this->psrContainer = new PsrContainer($this->container);
	}

	/**
	 * @param $name
	 * @param $handle
	 * @param mixed ...$params
	 * @return bool
	 */
	public function set($name, $handle, ...$params) {
		if (is_string($handle)) {
			$handle = function () use ($handle, $params) {
				return new $handle(...$params);
			};
		}
		$this->container[$name] = $handle;
	}

	/**
	 * @param $name
	 * @param array $params  当参数为标量或者数组时，可按参数进行单例
	 * @return mixed
	 */
	public function get($name, array $params = []) {
		$support = true;
		foreach ($params as $param) {
			if (!is_scalar($param) && !is_array($param)) {
				$support = false;
			}
		}
		if (!$support) {
			throw new \RuntimeException('when an object is included in a parameter, it cannot be singularized by a parameter');
		}
		$instanceKey = $name;
		if ($support && $params) {
			$instanceKey = md5($instanceKey . json_encode($params));
		}
		if (!$this->has($instanceKey)) {
			$this->set($instanceKey, $name, ...$params);
		}

		return $this->psrContainer->get($instanceKey);
	}

	public function has($name) {
		return $this->psrContainer->has($name);
	}

	public function delete($name) {
		if ($this->has($name)) {
			unset($this->container[$name]);
		}
	}

	/**
	 * @deprecated
	 * @param $name
	 * @return mixed
	 */
	public function singleton($name) {
		return $this->get($name);
	}

	public function clear() {
		foreach ($this->container->keys() as $key) {
			$this->delete($key);
		}
	}

	public function __call($name, $arguments) {
		return $this->container->$name(...$arguments);
	}
}
