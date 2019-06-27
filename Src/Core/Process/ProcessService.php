<?php

namespace W7\Core\Process;

use W7\Core\Process\Pool\PoolServiceAbstract;

class ProcessService extends PoolServiceAbstract {
	const DEFAULT_PID_FILE = '/tmp/swoole_user_process.pid';
	private $userProcess;


	public function __construct() {
		$this->config = iconfig()->getUserConfig('process');
		$this->config['setting']['pid_file'] = empty($this->config['setting']['pid_file']) ? self::DEFAULT_PID_FILE : $this->config['setting']['pid_file'];
		$this->poolConfig = $this->config['setting'];
	}

	/**
	 * 指定需要启动的process名称,只启动enable为true的process
	 *
	 * @param [type] $process
	 * @return void
	 */
	public function setUserProcess($process) {
		$processConfig = iconfig()->getUserConfig('process')['process'];
		$process = explode(',', $process);
		foreach ($process as $key => $item) {
			if (empty($processConfig[$item]) || !$processConfig[$item]['enable']) {
				throw new \Exception('the process ' . $item . ' does not exist or is disable');
			}
			$this->userProcess[$item] = $processConfig[$item];
		}
	}

	public function getUserProcess() {
		if (!$this->userProcess) {
			$process = iconfig()->getUserConfig('process')['process'];
			foreach ($process as $key => $item) {
				if ($process[$key]['enable']) {
					$this->userProcess[$key] = $item;
				}
			}
		}

		return $this->userProcess;
	}

	public function start() {
		$userProcess = $this->getUserProcess();
		if (!$userProcess) {
			return false;
		}

		foreach ($userProcess as $name => $process) {
			$this->processPool->registerProcess($name, $process['class'], $process['number']);
		}
		$this->processPool->start();
	}
}