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

class ResourceRoute {
	private ResourceRegister $register;
	private string $name;
	private string $controller;
	private array $options;
	private bool $registered;

	public function __construct(ResourceRegister $register, $name, $controller, $options = []) {
		$this->register = $register;
		$this->name = $name;
		$this->controller = $controller;
		$this->options = $options;
	}

	public function only($actions): static {
		$this->options['only'] = is_array($actions) ? $actions : func_get_args();

		return $this;
	}

	public function except($actions): static {
		$this->options['except'] = is_array($actions) ? $actions : func_get_args();

		return $this;
	}

	public function names($names): static {
		$this->options['names'] = $names;

		return $this;
	}

	public function name($action, $name): static {
		$this->options['names'][$action] = $name;

		return $this;
	}

	public function parameters($parameters): static {
		$this->options['parameters'] = $parameters;

		return $this;
	}

	public function parameter($previous, $new): static {
		$this->options['parameters'][$previous] = $new;

		return $this;
	}

	public function middleware($middleware): static {
		$this->options['middleware'] = $middleware;

		return $this;
	}

	public function register() {
		$this->registered = true;

		return $this->register->register(
			$this->name,
			$this->controller,
			$this->options
		);
	}

	/**
	 * Perform automatic registration if manual registration is not available
	 */
	public function __destruct() {
		if (! $this->registered) {
			$this->register();
		}
	}
}
