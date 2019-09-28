<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Crontab\Process;

use W7\Core\Dispatcher\TaskDispatcher;
use W7\Core\Process\ProcessAbstract;

class CrontabExecutor extends ProcessAbstract {
	public function run() {
		while ($data = $this->getMsg()) {
			if ($data) {
				/**
				 * @var TaskDispatcher $taskDispatcher
				 */
				ilogger()->info('pop crontab task ' .$data . ' at ' . $this->process->pid);
				$taskDispatcher = iloader()->get(TaskDispatcher::class);
				try {
					$result = $taskDispatcher->dispatch($this->process, -1, $this->process->pid, $data);
					if ($result === false) {
						continue;
					}
					ilogger()->info('complete crontab task ' . $result->task . ' with data ' .$data . ' at ' . $this->process->pid);
				} catch (\Throwable $e) {
					ilogger()->info('exec crontab task ' . $result->task . ' with data ' .$data . ' at ' . $this->process->pid . ' with erro ' . $e->getMessage());
				}
			}
		}
	}
}
