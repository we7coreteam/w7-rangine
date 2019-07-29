<?php

/**
 * WeEngine Api System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
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
