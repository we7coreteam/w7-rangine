<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\View\Handler;

use Twig\Loader\FilesystemLoader;
use W7\App;
use W7\Core\Exception\DumpException;

abstract class HandlerAbstract {
	protected $config = [];
	protected static $providerTemplatePath = [];
	protected static $defaultTemplatePath;
	protected static $defaultCachePath;
	const DEFAULT_NAMESPACE = FilesystemLoader::MAIN_NAMESPACE;
	const __STATIC__ = '__STATIC__';
	const __CSS__ = '__CSS__';
	const __JS__ = '__JS__';
	const __IMAGES__ = '__IMAGES__';

	public function __construct(array $config) {
		$this->config = $config;

		$this->initTemplatePath();
		$this->initCachePath();
		$this->init();
		$this->registerSystemFunction();
		$this->registerSystemConst();
		$this->registerSystemObject();
	}

	protected function initTemplatePath() {
		if (!static::$defaultTemplatePath) {
			//通过provider注册时把provider的path加进来
			static::$providerTemplatePath = $this->config['provider_template_path'];
			static::$defaultTemplatePath = APP_PATH . '/View';
		}
	}

	protected function initCachePath() {
		if (empty($this->config['cache'])) {
			return false;
		}

		if (!static::$defaultCachePath) {
			if ($this->config['cache'] === true) {
				static::$defaultCachePath = static::$defaultTemplatePath . '/cache';
			} else {
				static::$defaultCachePath = $this->config['cache'];
			}
		}
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
			try {
				idd(...func_get_args());
			} catch (DumpException $e) {
				echo $e->getMessage();
			}
		});
		$this->registerFunction('itrans', function () {
			return itrans(...func_get_args());
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

	abstract public function render($namespace, $name, $context = []) : string;
}
