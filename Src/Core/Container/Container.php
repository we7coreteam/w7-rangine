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
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface {
	private $container;
	private $psrContainer;
	private $deferredServices = [];
	private $deferredServiceLoaders = [];

	public function __construct() {
		$this->container = new PimpleContainer();
		$this->psrContainer = new PsrContainer($this->container);
	}

	public function registerDeferredService($services) {
		$services = (array)$services;
		$this->deferredServices = array_unique(array_merge($this->deferredServices, $services));
	}

	public function registerDeferredServiceLoader(\Closure $loader) {
		$this->deferredServiceLoaders[] = $loader;
	}

	public function loadDeferredService($service) {
		if (in_array($service, $this->deferredServices)) {
			//If triggered once, do not trigger the next time
			unset($this->deferredServices[array_search($service, $this->deferredServices)]);
			foreach ($this->deferredServiceLoaders as $loader) {
				$loader($service);
			}
		}
	}

	/**
	 * @param $name
	 * @param $handle
	 * @param mixed ...$params
	 * @return void
	 */
	public function set($name, $handle, ...$params) {
		if (is_string($handle) && class_exists($handle)) {
			$handle = function () use ($handle, $params) {
				return new $handle(...$params);
			};
		}
		$this->container[$name] = $handle;
	}

	public function has($name) {
		//Detects whether a lazy load service is present and triggers the loader
		$this->loadDeferredService($name);

		return $this->psrContainer->has($name);
	}

	/**
	 * @param $name
	 * @param array $params  When the argument is a scalar or an array, the singleton can be performed by the argument
	 * @return mixed
	 */
	public function get($name, array $params = []) {
		if (!$this->has($name)) {
			//If the name here is not the class name, it cannot be used
			$this->set($name, $name, ...$params);
		}

		return $this->psrContainer->get($name);
	}

	public function append($dataKey, array $value, $default = []) {
		if (!$this->has($dataKey)) {
			$this->set($dataKey, $default);
		}
		$data = $this->get($dataKey) ?? [];

		if (is_object($data)) {
			foreach ($value as $key => $item) {
				$data->$key = $item;
			}
		} elseif (is_array($data)) {
			foreach ($value as $key => $item) {
				$data[$key] = $item;
			}
		} else {
			throw new \RuntimeException('Only append data to array and object');
		}
		$this->set($dataKey, $data);
	}

	public function clone($name, array $params = []) {
		return clone $this->get($name, $params);
	}

	public function delete($name) {
		if ($this->has($name)) {
			unset($this->container[$name]);
		}
	}

	/**
	 * Semantic alias used to handle singleton objects
	 * @param $name
	 * @param array $params
	 * @return mixed
	 */
	public function singleton($name, array $params = []) {
		return $this->get($name, $params);
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
