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

class RegisterSecurityDirBootstrap implements BootstrapInterface {
	public function bootstrap(App $app) {
		//设置安全限制目录
		$openBaseDirConfig = $app->getConfigger()->get('app.setting.basedir', []);
		if (is_array($openBaseDirConfig)) {
			$openBaseDirConfig = implode(PATH_SEPARATOR, $openBaseDirConfig);
		}

		$openBaseDir = [
			'/tmp',
			sys_get_temp_dir(),
			BASE_PATH,
			$openBaseDirConfig,
			session_save_path()
		];
		ini_set('open_basedir', implode(PATH_SEPARATOR, $openBaseDir));
	}
}
