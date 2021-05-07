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

namespace W7\Core\Route;

use W7\Contract\Router\RouterInterface;
use W7\Core\Helper\FileLoader;

class RouteMapping {
	/**
	 * @var RouterInterface
	 */
	protected $router;
	/**
	 * @var FileLoader
	 */
	protected $fileLoader;

	private static $isInitRouteByConfig = false;

	public function __construct(RouterInterface $router, FileLoader $fileLoader) {
		$this->router = $router;
		$this->fileLoader = $fileLoader;
	}

	/**
	 * @return array|mixed
	 */
	public function getMapping($routeFileDir) {
		if (!self::$isInitRouteByConfig) {
			//Prevent duplicate registration when multiple services are started simultaneously
			$this->loadRouteConfig($routeFileDir);
			self::$isInitRouteByConfig = true;
		}
		$this->registerSystemRoute();
		return $this->router->getData();
	}

	protected function loadRouteConfig($routeFileDir) {
		$configFileTree = glob($routeFileDir . '/*.php');
		if (empty($configFileTree)) {
			return true;
		}

		foreach ($configFileTree as $path) {
			$this->fileLoader->loadFile($path);
		}

		return true;
	}

	//If the user has customized the system route, follow the user's route
	public function registerSystemRoute() {
		try {
			$this->router->get('/favicon.ico', ['\W7\Core\Controller\FaviconController', 'index']);
		} catch (\Throwable $e) {
		}
	}
}
