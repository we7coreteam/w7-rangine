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

use W7\Contract\View\ViewInterface;
use W7\Core\View\Handler\HandlerAbstract;
use W7\Core\View\Handler\TwigHandler;

class View implements ViewInterface {
	private $config;
	private $customFunctions = [];
	private $customConsts = [];
	private $customObjs = [];

	public function __construct($config = []) {
		$this->config = $config;
		$this->pretreatmentConfig();
	}

	private function pretreatmentConfig() {
		$this->config['debug'] = $this->config['debug'] ?? false;
		$this->config['suffix'] = empty($this->config['suffix']) ? 'html' : $this->config['suffix'];
		$this->config['template_path'][HandlerAbstract::DEFAULT_NAMESPACE][] = APP_PATH . '/View';

		$userTemplatePath = (array)($this->config['template_path'] ?? []);
		$this->config['template_path'] = [];
		foreach ($userTemplatePath as $namespace => $paths) {
			$paths = (array)$paths;
			$namespace = is_numeric($namespace) ? HandlerAbstract::DEFAULT_NAMESPACE : $namespace;
			$this->config['template_path'][$namespace] = $paths;
		}
	}

	public function addTemplatePath(string $namespace, string $path) {
		$this->config['template_path'][$namespace][] = $path;
	}

	public function getViewSuffix() {
		return $this->config['suffix'];
	}

	public function registerFunction($name, \Closure $callback) {
		$this->customFunctions[$name] = $callback;
	}

	public function registerConst($name, $value) {
		$this->customConsts[$name] = $value;
	}

	public function registerObject($name, $object) {
		$this->customObjs[$name] = $object;
	}

	private function getHandler() : HandlerAbstract {
		$handler = empty($this->config['handler']) ? TwigHandler::class : $this->config['handler'];
		$handler = new $handler($this->config);
		if (!($handler instanceof HandlerAbstract)) {
			throw new \RuntimeException('view handler must instance of HandlerAbstract');
		}

		return $handler;
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

	private function parseViewName($name) {
		if (isset($name[0]) && '@' == $name[0]) {
			if (false === $pos = strpos($name, DIRECTORY_SEPARATOR)) {
				throw new \RuntimeException(sprintf('Malformed namespaced template name "%s" (expecting "@namespace/template_name").', $name));
			}

			$namespace = substr($name, 1, $pos - 1);
			$name = substr($name, $pos + 1);

			return [$namespace, $name];
		}

		return [HandlerAbstract::DEFAULT_NAMESPACE, $name];
	}

	public function render($name, $context = []) : string {
		$handler = $this->getHandler();
		$this->addResourceToHandler($handler);
		[$namespace, $name] = $this->parseViewName($name);
		return $handler->render($namespace, $name . '.'. $this->getViewSuffix(), $context);
	}
}
