<?php


namespace W7\Core\Process\Pool;

use Swoole\Process;
use W7\App;
use W7\Core\Process\ProcessAbstract;

/**
 * 该进程池会随server一起启动,并由server管理
 * Class DependentPool
 * @package W7\Core\Process\Pool
 */
class DependentPool extends PoolAbstract {
	public function start(){
		for ($i = 0; $i < $this->processFactory->count(); $i++) {
			$process = $this->processFactory->make($i);
			$process->setManagerType(ProcessAbstract::DEPENDENT_MANAGER);

			$swooleProcess = new Process(function (Process $worker) use ($process) {
				$process->setProcess($worker);
				if ($this->mqKey) {
					$process->setMq($this->mqKey);
				}

				$process->onStart();
			}, false, SOCK_DGRAM);

			App::$server->getServer()->addProcess($swooleProcess);
		}
	}
}