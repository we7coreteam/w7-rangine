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

	protected $canStart = false;

	public function __construct() {
		$this->init();
	}

	protected function init() {}

	private function checkSetting() {
		if ($this->processPool instanceof IndependentPool) {
			if (empty($this->config['setting']['pid_file'])) {
				throw new \Exception('setting/pid_file parameter error');
			}
		}
	}

	abstract protected function register() : bool;

	public function registerPool($class) {
		$this->processPool = new $class($this->poolConfig);
		if (!($this->processPool instanceof PoolAbstract)) {
			throw new \Exception('the pool must be instance PoolAbstract');
		}

		$this->checkSetting();

		$this->canStart = $this->register();
		return $this;
	}

	public function start() {
		if ($this->canStart) {
			$this->processPool->start();
		}
	}

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
			'masterPid' => $pid,
			'child_process_num' => $this->processPool->getProcessFactory()->count()
		];
	}

	public function getPool() : PoolAbstract{
		return $this->processPool;
	}
}