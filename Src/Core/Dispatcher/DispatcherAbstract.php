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

use W7\Contract\Dispatcher\DispatcherInterface;
use W7\Core\Helper\Traiter\AppCommonTrait;

abstract class DispatcherAbstract implements DispatcherInterface {
	use AppCommonTrait;

	/**
	 * 派发服务
	 * @param mixed ...$params
	 */
	public function dispatch(...$params) {
	}
}
