<?php


namespace W7\Core\Process\Pool;

use Swoole\Process;
use W7\App;

class ServerPool extends PoolAbstract {
	private $mqKey = 0;

	protected function init(){
		$this->mqKey = $this->config['mq_key'] ?? 0;
	}

	public function start(){
		for ($i = 0; $this->container->count(); $i++) {
			$process = $this->container->make($i);
			if ($this->mqKey) {
				$process->setMq($this->mqKey);
			}
			$swooleProcess = new Process(function (Process $worker) use ($process) {
				$process->setProcess($worker);
				$process->start();
			}, false, SOCK_DGRAM);

			App::$server->getServer()->addProcess($swooleProcess);
		}
	}
}