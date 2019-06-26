<?php

namespace W7\Core\Process;

use W7\Core\Process\Pool\PoolServiceAbstract;
use W7\Core\Process\Process\ReloadProcess;

class ProcessService extends PoolServiceAbstract {
	const DEFAULT_PID_FILE = '/tmp/swoole_user_process.pid';

	public function __construct() {
		$this->config = iconfig()->getUserConfig('process');
		$this->config['setting']['pid_file'] = empty($this->config['setting']['pid_file']) ? self::DEFAULT_PID_FILE : $this->config['setting']['pid_file'];
		$this->poolConfig = $this->config['setting'];
	}

	public function start() {
		$process = 0;
		if ((ENV & DEBUG) === DEBUG) {
			$this->config['process'][static::$group]['reload'] = [
				'enable' => true,
				'class' => ReloadProcess::class,
				'number' => 1
			];
		}
		foreach ($this->config['process'][static::$group] as $name => $process) {
			if ($process['enable']) {
				$this->processPool->registerProcess($name, $process['class'], $process['number']);
				++$process;
			}
		}
		if ($process == 0) {
			throw new \Exception('process not be empty');
		}


		$this->processPool->start();
	}

	public function stop() {
		$this->processPool->stop();
	}
}