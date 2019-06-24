<?php

namespace W7\Core\Crontab\Process;

use Swoole\Process;
use W7\Core\Dispatcher\TaskDispatcher;
use W7\Core\Process\ProcessAbstract;

class CrontabExecutor extends ProcessAbstract {
	public function run(Process $process) {
		while($data = $process->pop()){
			if ($data) {
				/**
				 * @var TaskDispatcher $taskDispatcher
				 */
				ilogger()->info('pop crontab task ' .$data . ' at ' . $process->pid);
				$taskDispatcher = iloader()->singleton(TaskDispatcher::class);
				$result = $taskDispatcher->dispatch($process, -1 , $process->pid, $data);
				if ($result === false) {
					continue;
				}
				ilogger()->info('complete crontab task ' . $result->task . ' with data ' .$data . ' at ' . $process->pid . ' with $result ' . $result->error);
			}
		}

		sleep(1);
	}

	public function stop(Process $process) {
		ilogger()->info('crontab executor process exit');
	}
}