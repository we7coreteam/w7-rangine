<?php

namespace W7\Core\Helper\Traiter;

use W7\App;

trait InstanceTrait {
	public static function instance() {
		$instance = App::getApp()->getContainer()->get(static::class);

		/**
		 * @var static $instance
		 */
		return $instance;
	}
}