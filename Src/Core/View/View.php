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
	private $customFunctions = [];
	private $customConsts = [];
	private $customObjs = [];

	public function __construct() {
		$this->config = iconfig()->getUserAppConfig('view');
		$this->config['suffix'] = empty($this->config['suffix']) ? 'html' : $this->config['suffix'];
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

	public function getSuffix() {
		return $this->config['suffix'];
	}

	public function addProviderTemplatePath($namespace, $path) {
		$this->config['provider_template_path'] = (array)($this->config['provider_template_path'] ?? []);
		if (!empty($this->config['provider_template_path'][$namespace])) {
			throw new \RuntimeException('the namespace ' . $namespace . ' is exist');
		}
		$this->config['provider_template_path'][$namespace] = $path;
	}

	public function registerFunction($name, $callback) {
		$this->customFunctions[$name] = $callback;
	}

	public function registerConst($name, $value) {
		$this->customConsts[$name] = $value;
	}

	public function registerObject($name, $object) {
		$this->customObjs[$name] = $object;
	}

	private function addResourceToHandler(HandlerAbstract $handler) {
		foreach ($this->customFunctions as $name => $callback) {
			$handler->registerFunction($name, $callback);
		}
		foreach ($this->customConsts as $name => $value) {
			$handler->registerConst($name, $value);
		}
		foreach ($this->customObjs as $name => $object) {
			$handler->registerObject($name, $object);
		}
	}

	public function render($name, $context = []) {
		$handler = $this->getHandler();
		$this->addResourceToHandler($handler);
		return $handler->render($name . '.'. $this->getSuffix(), $context);
	}
}
