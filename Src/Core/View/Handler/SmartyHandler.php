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

class SmartyHandler extends HandlerAbstract {
	/**
	 * @var \Smarty
	 */
	private $smarty;

	protected function init() {
		$this->smarty = new \Smarty();
		$this->smarty->setTemplateDir(self::$templatePath);
		$this->smarty->setCacheDir($this->config['cache_path'] ?? self::$templatePath[0] . '/cache');
		$this->smarty->setCompileDir($this->config['compiler_path'] ?? self::$templatePath[0] . '/compiler');
		$this->smarty->debugging = $this->config['debug'];
		if (!empty($this->config['cache'])) {
			$this->smarty->caching = 1;
			$this->smarty->setCacheLifetime($this->config['life_time'] ?? $this->smarty->cache_lifetime);
		}
	}

	public function registerConst($name, $value) {
		$this->smarty->assign($name, $value);
	}

	public function registerFunction($name, \Closure $callback) {
		$this->smarty->registerPlugin(\Smarty::PLUGIN_FUNCTION, $name, $callback);
	}

	public function registerObject($name, $object) {
		$this->smarty->registerObject($name, $object, null, false);
	}

	public function render($name, $context = []): string {
		foreach ($context as $key => $item) {
			$this->smarty->assign($key, $item);
		}

		return $this->smarty->fetch($name);
	}
}
