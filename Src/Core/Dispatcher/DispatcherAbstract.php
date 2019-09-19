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

namespace W7\Core\Dispatcher;

abstract class DispatcherAbstract {
	/**
	 * 派发服务
	 * @param mixed ...$params
	 */
	public function dispatch(...$params) {
	}

	/**
	 * 注册服务
	 */
	public function register() {
	}
}
