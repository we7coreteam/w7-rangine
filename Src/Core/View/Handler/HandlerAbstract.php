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
	protected static $config;
	protected static $templatePath;

	public function __construct() {
		$this->initConfig();
		$this->init();
	}

	private function initConfig() {
		if (static::$templatePath) {
			return true;
		}
		static::$config = iconfig()->getUserAppConfig('view');
		static::$templatePath = BASE_PATH . '/view';
	}

	protected function init() {
	}

	abstract public function render($name, $context = []) : string;
}
