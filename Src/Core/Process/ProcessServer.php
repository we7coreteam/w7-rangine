<?php

namespace W7\Core\Process;

use W7\Core\Process\Pool\IndependentPool;
use W7\Core\Process\Pool\PoolServerAbstract;

class ProcessServer extends PoolServerAbstract {
	const DEFAULT_PID_FILE = '/tmp/swoole_user_process.pid';
	private $userProcess;


	protected function init() {
		$this->config = iconfig()->getUserConfig('process');
		$this->config['setting']['pid_file'] = empty($this->config['setting']['pid_file']) ? self::DEFAULT_PID_FILE : $this->config['setting']['pid_file'];
		$this->poolConfig = $this->config['setting'];
	}

	protected function register() : bool {
		$userProcess = $this->getUserProcess();
		if (!$userProcess) {
			return false;
		}

		foreach ($userProcess as $name => $process) {
			//在不随server启动下,过滤reload进程
			if ($name === 'reload' && $this->processPool instanceof IndependentPool) {
				continue;
			}

			$this->processPool->registerProcess($name, $process['class'], $process['number']);
		}

		return true;
	}

	public function getType() {
		return parent::TYPE_PROCESS;
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
}