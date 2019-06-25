<?php

namespace W7\Core\UserProcess\Server;

use W7\Core\Process\Pool\Pool;
use W7\Core\UserProcess\Process\ReloadProcess;

class ProcessServer {
	const DEFAULT_PID_FILE = '/tmp/swoole_user_process.pid';
	private $processPool;
	private $config;

	public function __construct() {
		$this->config = iconfig()->getUserConfig('process');
		$this->config['setting']['pid_file'] = empty($this->config['setting']['pid_file']) ? self::DEFAULT_PID_FILE : $this->config['setting']['pid_file'];

		$this->processPool = new Pool($this->config['setting']);
	}

	public function start() {
		if ((ENV & DEBUG) === DEBUG) {
			$this->config['process']['reload'] = [
				'enable' => true,
				'class' => ReloadProcess::class,
				'number' => 1
			];
		}

		foreach ($this->config['process'] as $name => $process) {
			if ($process['enable']) {
				$this->processPool->addProcess($name, $process['class'], $process['number']);
			}
		}
		$this->processPool->start();
	}

	public function stop() {
		$this->processPool->stop();
	}
}