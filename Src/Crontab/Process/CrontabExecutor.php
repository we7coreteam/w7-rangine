<?php

namespace W7\Crontab\Process;

use W7\Core\Dispatcher\TaskDispatcher;
use W7\Core\Process\ProcessAbstract;

class CrontabExecutor extends ProcessAbstract {
	public function run() {
		while($data = $this->process->pop()){
			if ($data) {
				/**
				 * @var TaskDispatcher $taskDispatcher
				 */
				ilogger()->info('pop crontab task ' .$data . ' at ' . $this->process->pid);
				$taskDispatcher = iloader()->get(TaskDispatcher::class);
				try{
					$result = $taskDispatcher->dispatch($this->process, -1 , $this->process->pid, $data);
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