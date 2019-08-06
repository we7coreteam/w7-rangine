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

use W7\Core\Process\ProcessServerAbstract;
use W7\Reload\Process\ReloadProcess;

class Server extends ProcessServerAbstract {
	public function getType() {
		return parent::TYPE_RELOAD;
	}

	protected function register() {
		$this->pool->registerProcess('reload', ReloadProcess::class, 1);
	}

	public function start() {
		throw new \Exception('cannot start alone');
	}
}
