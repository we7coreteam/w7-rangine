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

use Illuminate\Contracts\Container\BindingResolutionException;
use W7\Core\Helper\Storage\Context;

class Container extends \Illuminate\Container\Container {
	/**
	 * @var Context
	 */
	protected $context;
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
	 * @return void
	 */
	public function set($name, $handle, $shared = true) {
		if (is_object($handle) && (!$handle instanceof \Closure)) {
			$this->instance($name, $handle);
		} else {
			$this->bind($name, $handle, $shared);
		}
	}

	public function has($name) {
		//Detects whether a lazy load service is present and triggers the loader
		$this->loadDeferredService($name);

		return parent::has($name);
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public function get($name) {
		if (!$this->has($name)) {
			//If the name here is not the class name, it cannot be used
			$this->set($name, $name);
		}

		return parent::get($name);
	}

	public function clone($name) {
		return clone $this->get($name);
	}

	public function delete($name) {
		$abstract = $this->getAlias($name);
		unset($this[$abstract]);
	}

	public function clear() {
		$this->flush();
	}

	/**
	 * Resolve the given type from the container.
	 *
	 *
	 * @param  string  $abstract
	 * @param  array  $parameters
	 * @param  bool  $raiseEvents
	 * @return mixed
	 *
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	protected function resolve($abstract, $parameters = [], $raiseEvents = true) {
		$abstract = $this->getAlias($abstract);

		$this->loadDeferredService($abstract);

		$needsContextualBuild = ! empty($parameters) || ! is_null(
			$this->getContextualConcrete($abstract)
		);

		// If an instance of the type is currently being managed as a singleton we'll
		// just return an existing instance instead of instantiating new instances
		// so the developer can keep using the same objects instance every time.
		if (isset($this->instances[$abstract]) && ! $needsContextualBuild) {
			return $this->instances[$abstract];
		}

		$cId = $this->getContext()->getCoroutineId();
		$this->with[$cId][] = $parameters;

		$concrete = $this->getConcrete($abstract);

		// We're ready to instantiate an instance of the concrete type registered for
		// the binding. This will instantiate the types, as well as resolve any of
		// its "nested" dependencies recursively until all have gotten resolved.
		if ($this->isBuildable($concrete, $abstract)) {
			$object = $this->build($concrete);
		} else {
			$object = $this->make($concrete);
		}

		// If we defined any extenders for this type, we'll need to spin through them
		// and apply them to the object being built. This allows for the extension
		// of services, such as changing configuration or decorating the object.
		foreach ($this->getExtenders($abstract) as $extender) {
			$object = $extender($object, $this);
		}

		// If the requested type is registered as a singleton we'll want to cache off
		// the instances in "memory" so we can return it later without creating an
		// entirely new instance of an object on each subsequent request for it.
		if ($this->isShared($abstract) && ! $needsContextualBuild) {
			$this->instances[$abstract] = $object;
		}

		if ($raiseEvents) {
			$this->fireResolvingCallbacks($abstract, $object);
		}

		// Before returning, we will also set the resolved flag to "true" and pop off
		// the parameter overrides for this build. After those two things are done
		// we will be ready to return back the fully constructed class instance.
		$this->resolved[$abstract] = true;

		array_pop($this->with[$cId]);

		return $object;
	}

	/**
	 * Instantiate a concrete instance of the given type.
	 *
	 * @param  string  $concrete
	 * @return mixed
	 *
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	public function build($concrete) {
		// If the concrete type is actually a Closure, we will just execute it and
		// hand back the results of the functions, which allows functions to be
		// used as resolvers for more fine-tuned resolution of these objects.
		if ($concrete instanceof \Closure) {
			return $concrete($this, $this->getLastParameterOverride());
		}

		try {
			$reflector = new \ReflectionClass($concrete);
		} catch (\ReflectionException $e) {
			throw new BindingResolutionException("Target class [$concrete] does not exist.", 0, $e);
		}

		// If the type is not instantiable, the developer is attempting to resolve
		// an abstract type such as an Interface or Abstract Class and there is
		// no binding registered for the abstractions so we need to bail out.
		if (! $reflector->isInstantiable()) {
			return $this->notInstantiable($concrete);
		}

		$cId = $this->getContext()->getCoroutineId();
		$this->buildStack[$cId][] = $concrete;

		$constructor = $reflector->getConstructor();

		// If there are no constructors, that means there are no dependencies then
		// we can just resolve the instances of the objects right away, without
		// resolving any other types or dependencies out of these containers.
		if (is_null($constructor)) {
			array_pop($this->buildStack[$cId]);

			return new $concrete;
		}

		$dependencies = $constructor->getParameters();

		// Once we have all the constructor's parameters we can create each of the
		// dependency instances and then use the reflection instances to make a
		// new instance of this class, injecting the created dependencies in.
		try {
			$instances = $this->resolveDependencies($dependencies);
		} catch (BindingResolutionException $e) {
			array_pop($this->buildStack[$cId]);

			throw $e;
		}

		array_pop($this->buildStack[$cId]);

		return $reflector->newInstanceArgs($instances);
	}

	/**
	 * Get the last parameter override.
	 *
	 * @return array
	 */
	protected function getLastParameterOverride() {
		$cId = $this->getContext()->getCoroutineId();
		$with = $this->with[$cId] ?? [];
		return count($with) ? end($with) : [];
	}

	/**
	 * Find the concrete binding for the given abstract in the contextual binding array.
	 *
	 * @param  string  $abstract
	 * @return \Closure|string|null
	 */
	protected function findInContextualBindings($abstract) {
		$buildStack = $this->buildStack[$this->getContext()->getCoroutineId()] ?? [];
		return $this->contextual[end($buildStack)][$abstract] ?? null;
	}

	/**
	 * Throw an exception that the concrete is not instantiable.
	 *
	 * @param  string  $concrete
	 * @return void
	 *
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	protected function notInstantiable($concrete) {
		$cid = $this->getContext()->getCoroutineId();
		if (! empty($this->buildStack[$cid])) {
			$previous = implode(', ', $this->buildStack[$cid]);

			$message = "Target [$concrete] is not instantiable while building [$previous].";
		} else {
			$message = "Target [$concrete] is not instantiable.";
		}

		throw new BindingResolutionException($message);
	}

	protected function getContext() : Context {
		if (!$this->context) {
			$this->context = new Context();
			$this->set(Context::class, $this->context);
		}
		return $this->context;
	}
}
