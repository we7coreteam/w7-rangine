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

namespace W7\Core\Bootstrap;

use W7\App;
use W7\Core\Config\Config;
use W7\Core\Config\Env\Env;

class LoadConfigBootstrap implements BootstrapInterface {
	private $payload = [];

	public function getBuiltInConfigPath() {
		return BASE_PATH . '/vendor/composer/rangine/autoload/config';
	}

	public function bootstrap(App $app) {
		$loadDir = $app->getConfigCachePath();
		if (!file_exists($loadDir)) {
			$loadDir = BASE_PATH . '/config';
			(new Env(BASE_PATH))->load();
		}

		$this->loadConfigFile($this->getBuiltInConfigPath());
		$this->loadConfigFile($loadDir);

		$app->getContainer()->set(Config::class, function () {
			return new Config($this->payload);
		});
	}

	public function loadConfigFile($configDir) {
		$configFileTree = glob($configDir . '/*.php');
		if (empty($configFileTree)) {
			return $this->payload;
		}

		foreach ($configFileTree as $path) {
			$key = pathinfo($path, PATHINFO_FILENAME);
			$config = include $path;
			if (is_array($config)) {
				$this->payload[$key] = $this->payload[$key] ?? [];
				$this->payload[$key] = array_merge_recursive($this->payload[$key], $config);
			}
		}

		return $this->payload;
	}
}
