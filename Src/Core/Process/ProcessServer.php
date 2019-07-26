<?php

namespace W7\Core\Process;

use W7\Core\Process\Pool\DependentPool;
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
			$this->processPool->registerProcess($name, $process['class'], $process['number']);
		}

		return true;
	}

	public function getType() {
		return parent::TYPE_PROCESS;
	}

	private function getReloadProcess() {
		return [
			'name' => irandom(8) . '_reload',
			'enable' => true,
			'class' => ReloadProcess::class,
			'number' => 1
		];
	}

	public function getUserProcess() {
		if ((SERVER & PROCESS) === PROCESS) {
			$process = iconfig()->getUserConfig('process')['process'];
			foreach ($process as $key => $item) {
				if ($process[$key]['enable']) {
					$this->userProcess[$key] = $item;
				}
			}
		}
		if ($this->processPool instanceof DependentPool && (ENV & DEBUG) === DEBUG) {
			$reloadProcess = $this->getReloadProcess();
			$this->userProcess[$reloadProcess['name']] = $reloadProcess;
		}

		return $this->userProcess;
	}
}