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

use W7\Contract\Router\RouterInterface;
use W7\Core\Provider\ProviderAbstract;
use W7\Core\Route\Router;

class RouterProvider extends ProviderAbstract {
	public function register() {
		$this->container->set(RouterInterface::class, function () {
			$documentRoot = rtrim($this->config->get('server.common.document_root', BASE_PATH . '/public'), DIRECTORY_SEPARATOR);
			$enableStatic = $this->config->get('server.common.enable_static_handler', true);

			return new Router(null, [
				'document_root' => $documentRoot,
				'enable_static_handler' => $enableStatic
			]);
		});
	}

	public function providers(): array {
		return [RouterInterface::class];
	}
}
