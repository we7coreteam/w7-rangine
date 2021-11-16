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
 * The process pool starts with and is managed by the server
 * Class DependentPool
 * @package W7\Core\Process\Pool
 */
class DependentPool extends PoolAbstract {
	public function start() {
		if ($this->processFactory->count() === 0) {
			return false;
		}

		for ($i = 0; $i < $this->processFactory->count(); $i++) {
			$process = $this->processFactory->getById($i);
			if ($process->getProcess()) {
				continue;
			}

			$swooleProcess = new Process(function (Process $worker) use ($process, $i) {
				$process->setProcess($worker);
				(new ProcessStartListener())->run($process, $i, [
					'message_queue_key' => $this->mqKey
				]);
			}, false, SOCK_DGRAM, true);
			$process->setProcess($swooleProcess);

			App::$server->getServer()->addProcess($swooleProcess);
		}
	}
}
