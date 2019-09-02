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

use W7\Core\Process\ReloadProcess;
use W7\Core\Provider\ProviderAbstract;

class ViewProvider extends ProviderAbstract {
	public function register() {
		ReloadProcess::addType(iloader()->get(View::class)->getSuffix());
		ReloadProcess::addDir(BASE_PATH . '/view');
	}
}
