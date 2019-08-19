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

class SmartyHandler extends HandlerAbstract {
	/**
	 * @var \Smarty
	 */
	private $smarty;

	protected function init() {
		$this->smarty = new \Smarty();
		$this->smarty->setTemplateDir($this->templatePath);
		$this->smarty->setCacheDir($this->config['cache_path'] ?? $this->templatePath . '/cache');
		$this->smarty->setCompileDir($this->config['cache_path'] ?? $this->templatePath . '/compiler');

		if (!empty($this->config['cache'])) {
			$this->smarty->caching = 1;
			$this->smarty->setCacheLifetime($this->config['life_time'] ?? $this->smarty->cache_lifetime);
		}

		$this->registerPlugins();
		$this->registerConst();
	}

	private function registerPlugins() {
		$this->smarty->registerPlugin(\Smarty::PLUGIN_FUNCTION, 'idd', function () {
			$params = func_get_args();
			array_pop($params);
			return idd(array_pop($params));
		});
		$this->smarty->registerPlugin(\Smarty::PLUGIN_FUNCTION, 'irandom', function ($length, $numeric = false) {
			return irandom($length, $numeric);
		});
		$this->smarty->registerPlugin(\Smarty::PLUGIN_FUNCTION, 'getClientIp', function () {
			return getClientIp();
		});
		$this->smarty->registerPlugin(\Smarty::PLUGIN_FUNCTION, 'ienv', function ($key, $default = null) {
			return ienv($key, $default);
		});
		$this->smarty->registerObject('irequest', App::getApp()->getContext()->getRequest());
		$this->smarty->registerObject('icache', icache());
	}

	private function registerConst() {
		$this->smarty->assign('__STATIC__', $this->config['static'] ?? '/static/');
		$this->smarty->assign('__CSS__', $this->config['css'] ?? '/static/css/');
		$this->smarty->assign('__JS__', $this->config['js'] ?? '/static/js/');
		$this->smarty->assign('__IMAGES__', $this->config['images'] ?? '/static/images/');
	}

	public function render($name, $context = []): string {
		foreach ($context as $key => $item) {
			$this->smarty->assign($key, $item);
		}

		return $this->smarty->fetch($name);
	}
}
