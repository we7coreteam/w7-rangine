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
use W7\Core\Exception\DumpException;

abstract class HandlerAbstract {
	protected $config = [];
	protected static $providerTemplatePath = [];
	protected static $defaultTemplatePath;
	const DEFAULT_NAMESPACE = '__MAIN__';
	const __STATIC__ = '__STATIC__';
	const __CSS__ = '__CSS__';
	const __JS__ = '__JS__';
	const __IMAGES__ = '__IMAGES__';

	public function __construct(array $config) {
		$config['debug'] = (ENV & DEBUG) === DEBUG;
		$this->config = $config;

		$this->initTemplatePath();
		$this->init();
		$this->registerSystemFunction();
		$this->registerSystemConst();
		$this->registerSystemObject();
	}

	protected function initTemplatePath() {
		if (!static::$defaultTemplatePath) {
			//通过provider注册时把provider的path加进来
			static::$providerTemplatePath = (array)($this->config['provider_template_path'] ?? []);
			static::$defaultTemplatePath = BASE_PATH . '/view';
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

	protected function parseName($name) {
		if (isset($name[0]) && '@' == $name[0]) {
			if (false === $pos = strpos($name, '/')) {
				throw new \RuntimeException(sprintf('Malformed namespaced template name "%s" (expecting "@namespace/template_name").', $name));
			}

			$namespace = substr($name, 1, $pos - 1);
			$name = substr($name, $pos + 1);

			return [$namespace, $name];
		}

		return [static::DEFAULT_NAMESPACE, $name];
	}

	abstract public function render($name, $context = []) : string;
}
