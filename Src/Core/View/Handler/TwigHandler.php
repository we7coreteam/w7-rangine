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

use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TwigHandler extends HandlerAbstract {
	/**
	 * @var Environment
	 */
	private $twig;

	protected function init() {
		$loader = new FilesystemLoader(self::$templatePath, self::$templatePath[0]);
		$this->twig = new Environment($loader, $this->config);
		if ($this->config['debug']) {
			$this->twig->addExtension(new DebugExtension());
		}
	}

	public function registerFunction($name, \Closure $callback) {
		$this->twig->addFunction(new TwigFunction($name, $callback));
	}

	public function registerConst($name, $value) {
		$this->twig->addGlobal($name, $value);
	}

	public function registerObject($name, $object) {
		$this->twig->addGlobal($name, $object);
	}

	public function render($name, $context = []) : string {
		return $this->twig->render($name, $context);
	}
}
