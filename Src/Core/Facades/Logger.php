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

namespace W7\Core\Facades;

use W7\Core\Log\LogManager;

class Logger extends FacadeAbstract {
	protected static function getFacadeAccessor() {
		return LogManager::class;
	}

	public static function getFacadeRoot() {
		/**
		 * @var LogManager $root
		 */
		$root = parent::getFacadeRoot();
		return $root->getDefaultChannel();
	}
}
