<?php


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
	public function start(){
		for ($i = 0; $i < $this->processFactory->count(); $i++) {
			$swooleProcess = new Process(function (Process $worker) use ($i) {
				//这里不能通过event触发
				(new ProcessStartListener())->run($worker, $i, $this->processFactory, $this->mqKey);
			}, false, SOCK_DGRAM);

			App::$server->getServer()->addProcess($swooleProcess);
		}
	}
}