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

namespace W7\Core\Route\Provider;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteParser\Std;
use W7\App;
use W7\Contract\Router\RouterInterface;
use W7\Contract\Router\UrlGeneratorInterface;
use W7\Core\Provider\ProviderAbstract;
use W7\Core\Route\RouteCollector;
use W7\Core\Route\Router;
use W7\Core\Route\UrlGenerator;

class RouterProvider extends ProviderAbstract {
	public function register() {
		$routeCollector = new RouteCollector(new Std(), new GroupCountBased());
		$this->container->set(RouterInterface::class, function () use ($routeCollector) {
			$documentRoot = rtrim($this->config->get('server.common.document_root', App::getApp()->getBasePath() . '/public'), '/');
			$enableStatic = $this->config->get('server.common.enable_static_handler', true);

			return new Router($routeCollector, [
				'document_root' => $documentRoot,
				'enable_static_handler' => $enableStatic
			]);
		});

		$this->container->set(UrlGeneratorInterface::class, function () use ($routeCollector) {
			return new UrlGenerator($routeCollector, function () {
				return $this->getContext()->getRequest();
			});
		});
	}

	public function providers(): array {
		return [RouterInterface::class, UrlGeneratorInterface::class];
	}
}
