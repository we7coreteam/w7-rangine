<?php

namespace W7\Core\UserProcess\Server;

use W7\Core\Process\Pool\Pool;
use W7\Core\UserProcess\Process\ReloadProcess;

class ProcessServer {
	const DEFAULT_PID_FILE = '/tmp/swoole_user_process.pid';
	private $processPool;
	private $config;
	private static $group;

	public function __construct() {
		$this->config = iconfig()->getUserConfig('process');
		$this->config['setting']['pid_file'] = empty($this->config['setting']['pid_file']) ? self::DEFAULT_PID_FILE : $this->config['setting']['pid_file'];

		$this->processPool = new Pool($this->config['setting']);
	}

	public static function group($group) {
		static::$group = $group;
	}

	public function start() {
		if ((ENV & DEBUG) === DEBUG) {
			$this->config['process'][static::$group]['reload'] = [
				'enable' => true,
				'class' => ReloadProcess::class,
				'number' => 1
			];
		}
		if (empty($this->config['process'][static::$group])) {
			throw new \Exception('process not be empty');
		}

		foreach ($this->config['process'][static::$group] as $name => $process) {
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