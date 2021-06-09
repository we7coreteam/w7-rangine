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

namespace W7\Core\Helper\Traiter;

use W7\App;
use W7\Core\Helper\Storage\Context;

trait CoroutineInstanceTrait {
	public static function instance() {
		$contextKey = static::class;
		/**
		 * @var Context $context
		 */
		$context = App::getApp()->getContainer()->get(Context::class);
		$instance = $context->getContextDataByKey($contextKey);
		if (!$instance) {
			$instance = new $contextKey();
			$context->setContextDataByKey($contextKey, $instance);
		}

		/**
		 * @var static $instance
		 */
		return $instance;
	}
}
