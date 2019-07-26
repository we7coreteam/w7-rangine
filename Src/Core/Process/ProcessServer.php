<?php

namespace W7\Core\Process;

use W7\Core\Process\Pool\DependentPool;
use W7\Core\Process\Pool\IndependentPool;
use W7\Core\Process\Pool\PoolServerAbstract;
use W7\Core\Process\Process\ReloadProcess;

class ProcessServer extends PoolServerAbstract {
	private $userProcess;


	protected function init() {
		$this->config = iconfig()->getUserConfig('process');
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

	private function getReloadProcess() {
		return [
			'enable' => true,
			'class' => ReloadProcess::class,
			'number' => 1
		];
	}

	public function getUserProcess() {
		if (!$this->userProcess && (SERVER & PROCESS) === PROCESS) {
			$process = iconfig()->getUserConfig('process')['process'];
			foreach ($process as $key => $item) {
				if ($process[$key]['enable']) {
					$this->userProcess[$key] = $item;
				}
			}
		}
		if ($this->processPool instanceof DependentPool) {
			$this->userProcess['reload'] = $this->getReloadProcess();
			if ((ENV & RELEASE) === RELEASE) {
				unset($this->userProcess['reload']);
			}
		}

		return $this->userProcess;
	}
}