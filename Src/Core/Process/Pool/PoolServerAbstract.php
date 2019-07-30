<?php

/**
 * WeEngine Api System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Core\Process\Pool;

use W7\Core\Server\ServerInterface;

abstract class PoolServerAbstract implements ServerInterface {
	const TYPE_PROCESS = 'process';
	const TYPE_CRONTAB = 'crontab';
	const TYPE_RELOAD = 'reload';
	/**
	 * @var PoolAbstract
	 */
	protected $processPool;
	protected $config;
	protected $poolConfig;

	protected $canStart = false;

	public function __construct() {
		$this->init();
		$this->registerPool(IndependentPool::class);
	}

	protected function init() {
	}

	/**
	 * 添加子服务,只支持在http,tcp等server上添加
	 * @throws \Exception
	 */
	public function listener() {
		$this->registerPool(DependentPool::class)->start();
	}

	public function registerPool($class) {
		$this->processPool = new $class($this->poolConfig);
		if (!($this->processPool instanceof PoolAbstract)) {
			throw new \Exception('the pool must be instance PoolAbstract');
		}

		$this->canStart = $this->register();
		return $this;
	}

	abstract protected function register() : bool;

	private function checkSetting() {
		if ($this->processPool instanceof IndependentPool) {
			if (empty($this->config['setting']['pid_file'])) {
				throw new \Exception("config setting['pid_file'] parameter error");
			}
		}
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

	public function getPool() : PoolAbstract {
		return $this->processPool;
	}

	public function start() {
		$this->checkSetting();
		if ($this->canStart) {
			$this->processPool->start();
		}
	}

	public function stop() {
		return $this->processPool->stop();
	}
}
