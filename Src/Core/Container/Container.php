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

class Container extends \Illuminate\Container\Container {
	private $deferredServices = [];
	private $deferredServiceLoaders = [];

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
		$this->singleton($name, $handle);
	}

	public function has($name) {
		//Detects whether a lazy load service is present and triggers the loader
		$this->loadDeferredService($name);

		return parent::has($name);
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

		return parent::get($name);
	}

	public function clone($name, array $params = []) {
		return clone $this->get($name, $params);
	}

	/**
	 * Semantic alias used to handle singleton objects
	 * @param $name
	 * @param array $params
	 * @return mixed
	 */
	public function singleton($name, array $params = []) {
		return parent::singleton($name);
	}

	public function clear() {
		$this->flush();
	}
}
