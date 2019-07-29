<?php

namespace W7\Reload\Server;

use W7\Core\Process\Pool\PoolServerAbstract;
use W7\Reload\Process\ReloadProcess;

class ReloadServer extends PoolServerAbstract {
	public function getType() {
		return parent::TYPE_RELOAD;
	}

	protected function register(): bool {
		$this->processPool->registerProcess('reload', ReloadProcess::class, 1);
	}


}