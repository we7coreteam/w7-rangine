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

use W7\Core\Helper\Traiter\HandlerTrait;
use W7\Core\View\Handler\HandlerAbstract;

class View {
	use HandlerTrait;

	private $config;
	private $handlerClass;
	private $customFunctions = [];
	private $customConsts = [];
	private $customObjs = [];
	private $isTransform;

	public function __construct() {
		$this->config = iconfig()->get('app.view');
		$this->config['suffix'] = empty($this->config['suffix']) ? 'html' : $this->config['suffix'];
	}

	private function transformConfig() {
		if ($this->isTransform) {
			return false;
		}

		$this->config['debug'] = (ENV & DEBUG) === DEBUG;

		$this->config['provider_template_path'] = (array)($this->config['provider_template_path'] ?? []);
		$userTemplatePath = (array)($this->config['template_path'] ?? []);
		foreach ($userTemplatePath as $namespace => $paths) {
			$paths = (array)$paths;
			$namespace = is_numeric($namespace) ? HandlerAbstract::DEFAULT_NAMESPACE : $namespace;
			$this->config['provider_template_path'][$namespace] = $this->config['provider_template_path'][$namespace] ?? [];
			$this->config['provider_template_path'][$namespace] = array_merge($this->config['provider_template_path'][$namespace], $paths);
		}
		$this->isTransform = true;
	}

	private function getHandler() : HandlerAbstract {
		$class = $this->getHandlerClass();
		$this->transformConfig();
		return new $class($this->config);
	}

	private function getHandlerClass() {
		if (!$this->handlerClass) {
			$handler = $this->config['handler'] ?? 'twig';
			$this->handlerClass = $this->getHandlerClassByType('view', $handler);
		}

		return $this->handlerClass;
	}

	public function getSuffix() {
		return $this->config['suffix'];
	}

	public function addProviderTemplatePath(string $namespace, string $path) {
		$this->config['provider_template_path'] = (array)($this->config['provider_template_path'] ?? []);
		$this->config['provider_template_path'][$namespace][] = $path;
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

	private function parseName($name) {
		if (isset($name[0]) && '@' == $name[0]) {
			if (false === $pos = strpos($name, '/')) {
				throw new \RuntimeException(sprintf('Malformed namespaced template name "%s" (expecting "@namespace/template_name").', $name));
			}

			$namespace = substr($name, 1, $pos - 1);
			$name = substr($name, $pos + 1);

			return [$namespace, $name];
		}

		return [HandlerAbstract::DEFAULT_NAMESPACE, $name];
	}

	public function render($name, $context = []) {
		$handler = $this->getHandler();
		$this->addResourceToHandler($handler);
		[$namespace, $name] = $this->parseName($name);
		return $handler->render($namespace, $name . '.'. $this->getSuffix(), $context);
	}
}
