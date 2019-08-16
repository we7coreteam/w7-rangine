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

namespace W7\Core\View;

use W7\Core\View\Handler\HandlerAbstract;

class View {
	private $config;
	private $handlerClass;

	public function __construct() {
		$this->config = iconfig()->getUserAppConfig('view');
	}

	private function getHandler() : HandlerAbstract {
		$class = $this->getHandlerClass();
		return new $class($this->config);
	}

	private function getHandlerClass() {
		if (!$this->handlerClass) {
			$handler = $this->config['handler'] ?? 'twig';
			$class = sprintf('\\W7\\Core\\View\\Handler\\%sHandler', ucfirst($handler));
			if (!class_exists($class)) {
				$class = sprintf('\\W7\\App\\Handler\\View\\%sHandler', ucfirst($handler));
			}
			if (!class_exists($class)) {
				throw new \RuntimeException('view handler ' . $handler . ' is not support');
			}
			$this->handlerClass = $class;
		}

		return $this->handlerClass;
	}

	public function render($name, $context = []) {
		return $this->getHandler()->render($name, $context);
	}
}
