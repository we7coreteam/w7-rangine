<?php

namespace W7\Core\Process\Pool;

use W7\Core\Server\ServerInterface;

abstract class PoolServerAbstract implements ServerInterface {
	const TYPE_PROCESS = 'process';
	const TYPE_CRONTAB = 'crontab';
	/**
	 * @var PoolAbstract
	 */
	protected $processPool;
	protected $config;
	protected $poolConfig;

	public function registerPool($class) {
		$this->processPool = new $class($this->poolConfig);
		if (!($this->processPool instanceof PoolAbstract)) {
			throw new \Exception('the pool must be instance PoolAbstract');
		}

		return $this;
	}

	public function start() {}

	public function stop() {
		return $this->processPool->stop();
	}

	public function getServer() {
		return $this;
	}

	public function isRun() {
		$status = $this->getStatus();
		if (!empty($status['masterPid'])) {
			return true;
		} else {
			return false;
		}
	}

	public function getStatus() {
		$pid = 0;
		$pidFile = $this->poolConfig['pid_file'];
		if (file_exists($pidFile)) {
			$pid = file_get_contents($pidFile);
		}

		return [
			'masterPid' => $pid
		];
	}
}