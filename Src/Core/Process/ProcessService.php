<?php

namespace W7\Core\Process;

use W7\Core\Process\Pool\PoolServiceAbstract;

class ProcessService extends PoolServiceAbstract {
	const DEFAULT_PID_FILE = '/tmp/swoole_user_process.pid';

	public function __construct() {
		$this->config = iconfig()->getUserConfig('process');
		$this->config['setting']['pid_file'] = empty($this->config['setting']['pid_file']) ? self::DEFAULT_PID_FILE : $this->config['setting']['pid_file'];
		$this->poolConfig = $this->config['setting'];
	}

	public function start() {
		$num = 0;
		foreach ($this->config['process'] as $name => $process) {
			if ((!empty($this->config['appoint_process']) && $name == $this->config['appoint_process']) ||
				(empty($this->config['appoint_process']) && !empty($process['auto_start']))) {
				$this->processPool->registerProcess($name, $process['class'], $process['number']);
				++$num;
			}
		}
		if ($num == 0) {
			//表示是跟随server启动的方式
			if (empty($this->config['appoint_process'])) {
				return false;
			}
			throw new \Exception('the process list cannot be empty');
		}

		$this->processPool->start();
	}
}