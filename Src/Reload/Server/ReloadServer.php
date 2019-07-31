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

namespace W7\Reload\Server;

use W7\Core\Process\Pool\PoolServerAbstract;
use W7\Reload\Process\ReloadProcess;

class ReloadServer extends PoolServerAbstract {
	public function getType() {
		return parent::TYPE_RELOAD;
	}

	protected function register(): bool {
		$this->processPool->registerProcess('reload', ReloadProcess::class, 1);
		return true;
	}
}
