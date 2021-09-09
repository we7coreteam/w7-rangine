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

namespace W7\Core\Session\Provider;

use W7\App;
use W7\Contract\Session\SessionInterface;
use W7\Core\Provider\ProviderAbstract;
use W7\Core\Session\Session;

class SessionProvider extends ProviderAbstract {
	public function register() {
		$this->container->set(SessionInterface::class, function () {
			$config = $this->config->get('app.session', []);
			if (!empty($config['handler'])) {
				$config['handler'] = $this->config->get('handler.session.' . $config['handler'], $config['handler']);
			}
			if (!empty($config['channel'])) {
				$channel = sprintf('\\W7\\Core\\Session\\Channel\\%sChannel', ucfirst($config['channel']));
				if (!class_exists($channel)) {
					$channel = sprintf( App::getApp()->getAppNamespace() . '\\Channel\\Session\\%sChannel', ucfirst($config['channel']));
				}
				$config['channel'] = $channel;
			}

			return new Session($config);
		});
	}

	public function providers(): array {
		return [SessionInterface::class];
	}
}
