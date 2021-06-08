<?php

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