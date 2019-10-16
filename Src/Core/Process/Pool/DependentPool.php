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

namespace W7\Core\Process\Pool;

use Swoole\Process;
use W7\App;
use W7\Core\Listener\ProcessStartListener;

/**
 * 该进程池会随server一起启动,并由server管理
 * Class DependentPool
 * @package W7\Core\Process\Pool
 */
class DependentPool extends PoolAbstract {
	public function start() {
		if ($this->processFactory->count() == 0) {
			return false;
		}

		for ($i = 0; $i < $this->processFactory->count(); $i++) {
			$swooleProcess = new Process(function (Process $worker) use ($i) {
				//这里不能通过event触发
				(new ProcessStartListener())->run($worker, $i, $this->processFactory, $this->mqKey);
			}, false, SOCK_DGRAM);

			App::$server->getServer()->addProcess($swooleProcess);
		}
	}
}
