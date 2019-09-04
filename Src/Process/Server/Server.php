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

namespace W7\Process\Server;

use W7\Core\Process\ProcessServerAbstract;
use W7\Core\Server\ServerEnum;

class Server extends ProcessServerAbstract {
	public $canAddSubServer =  false;

	public function getType() {
		return ServerEnum::TYPE_PROCESS;
	}

	protected function register() {
		//虚拟配置
		$allProcess = iconfig()->getUserConfig('process');
		foreach ($allProcess as $process) {
			$this->pool->registerProcess($process['name'], $process['class'], $process['number']);
		}
	}
}
