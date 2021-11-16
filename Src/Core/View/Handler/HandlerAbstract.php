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

namespace W7\Core\View\Handler;

abstract class HandlerAbstract {
	public const DEFAULT_NAMESPACE = '__main__';
	public const __STATIC__ = '__STATIC__';
	public const __CSS__ = '__CSS__';
	public const __JS__ = '__JS__';
	public const __IMAGES__ = '__IMAGES__';

	protected array $config = [];

	public function __construct(array $config) {
		$this->config = $config;
	}

	abstract public function registerFunction($name, \Closure $callback);
	abstract public function registerConst($name, $value);
	abstract public function registerObject($name, $object);

	abstract public function render($namespace, $name, $context = []) : string;
}
