<?php

namespace W7\Process\Server;

use W7\Core\Process\Pool\PoolServerAbstract;

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

	public function getUserProcess() {
		$process = iconfig()->getUserConfig('process')['process'];
		foreach ($process as $key => $item) {
			if ($process[$key]['enable']) {
				$this->userProcess[$key] = $item;
			}
		}

		return $this->userProcess;
	}
}