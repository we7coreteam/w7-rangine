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

class RegisterRuntimeEnvBootstrap implements BootstrapInterface {
	public function bootstrap(App $app) {
		$defaultTimezone = $app->getConfigger()->get('app.setting.timezone', 'Asia/Shanghai');
		date_default_timezone_set($defaultTimezone);

		if (!is_dir(RUNTIME_PATH)) {
			mkdir(RUNTIME_PATH, 0777, true);
		}
		if (!is_readable(RUNTIME_PATH)) {
			throw new \RuntimeException('path ' . RUNTIME_PATH . ' no read permission');
		}
		if (!is_writeable(RUNTIME_PATH)) {
			throw new \RuntimeException('path ' . RUNTIME_PATH . ' no write permission');
		}

		$env = $app->getConfigger()->get('app.setting.env', DEVELOPMENT);
		!defined('ENV') && define('ENV', $env);
		if (!is_numeric(ENV) || ((RELEASE|DEVELOPMENT) & ENV) !== ENV) {
			throw new \RuntimeException("config setting['env'] error, please use the constant RELEASE, DEVELOPMENT, DEBUG, CLEAR_LOG, BACKTRACE instead");
		}
	}
}
