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

namespace W7\Core\Middleware;

class ControllerMiddlewareOptions {
	/**
	 * The middleware options.
	 *
	 * @var array
	 */
	protected $options;

	public function __construct(array &$options) {
		$this->options = &$options;
	}

	public function only($methods) {
		$this->options['only'] = is_array($methods) ? $methods : func_get_args();

		return $this;
	}

	public function except($methods) {
		$this->options['except'] = is_array($methods) ? $methods : func_get_args();

		return $this;
	}
}
