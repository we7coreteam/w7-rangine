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

use W7\App;

abstract class HandlerAbstract {
	protected $config;
	protected static $templatePath = [];
	const __STATIC__ = '__STATIC__';
	const __CSS__ = '__CSS__';
	const __JS__ = '__JS__';
	const __IMAGES__ = '__IMAGES__';

	public function __construct($config) {
		$this->config = $config;
		$this->initTemplatePath();
		$this->init();
		$this->registerSystemFunction();
		$this->registerSystemConst();
		$this->registerSystemObject();
	}

	protected function initTemplatePath() {
		if (!static::$templatePath) {
			$config = $this->config['template_path'] ?? '';
			$config = is_array($config) ? $config : [];
			array_unshift($config, BASE_PATH . '/view');
			static::$templatePath = $config;
		}

		return static::$templatePath;
	}

	protected function init() {
	}

	protected function registerSystemFunction() {
		$this->registerFunction('irandom', function () {
			return irandom(...func_get_args());
		});
		$this->registerFunction('getClientIp', function () {
			return getClientIp();
		});
		$this->registerFunction('ienv', function () {
			return ienv(...func_get_args());
		});
		$this->registerFunction('idd', function () {
			return idd(...func_get_args());
		});
	}

	protected function registerSystemConst() {
		$this->registerConst(HandlerAbstract::__STATIC__, $this->config['static'] ?? '/static/');
		$this->registerConst(HandlerAbstract::__CSS__, $this->config['css'] ?? '/static/css/');
		$this->registerConst(HandlerAbstract::__JS__, $this->config['js'] ?? '/static/js/');
		$this->registerConst(HandlerAbstract::__IMAGES__, $this->config['images'] ?? '/static/images/');
	}

	protected function registerSystemObject() {
		$this->registerObject('irequest', App::getApp()->getContext()->getRequest());
		$this->registerObject('icache', icache());
	}

	abstract public function registerFunction($name, \Closure $callback);
	abstract public function registerConst($name, $value);
	abstract public function registerObject($name, $object);

	abstract public function render($name, $context = []) : string;
}
