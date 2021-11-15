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
use W7\Contract\Config\RepositoryInterface;
use W7\Core\Config\Config;
use W7\Core\Config\Env\Env;

class LoadConfigBootstrap implements BootstrapInterface {
	private array $payload = [];
	protected array $ignoreFileNameMap = ['define'];

	public function bootstrap(App $app): void {
		if (!$app->configurationIsCached()) {
			$loadDir = $app->getBasePath() . '/config';
			(new Env($app->getBasePath()))->load();
		} else {
			$loadDir = $app->getConfigCachePath();
		}

		$this->loadConfigFile($app->getBuiltInConfigPath());
		$this->loadConfigFile($loadDir);

		$config = new Config($this->payload);
		$this->payload = [];
		$app->getContainer()->set(RepositoryInterface::class, $config);
	}

	public function loadConfigFile($configDir): array {
		$configFileTree = glob($configDir . '/*.php');
		if (empty($configFileTree)) {
			return $this->payload;
		}

		foreach ($configFileTree as $path) {
			$key = pathinfo($path, PATHINFO_FILENAME);
			if (in_array($key, $this->ignoreFileNameMap)) {
				continue;
			}
			$config = include $path;
			if (is_array($config)) {
				$this->payload[$key] = $this->payload[$key] ?? [];
				$this->payload[$key] = array_merge_recursive($this->payload[$key], $config);
			}
		}

		return $this->payload;
	}
}
