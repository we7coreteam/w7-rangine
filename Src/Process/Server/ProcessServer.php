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

namespace W7\Process\Server;

use W7\Core\Process\Pool\PoolServerAbstract;

class ProcessServer extends PoolServerAbstract {
	protected function init() {
		$this->config = iconfig()->getUserConfig('process');
		$this->poolConfig = $this->config['setting'];
	}

	public function getType() {
		return parent::TYPE_PROCESS;
	}

	public function getUserProcess() {
		$process = iconfig()->getUserConfig('process')['process'];
		$userProcess = [];
		foreach ($process as $key => $item) {
			if ($process[$key]['enable']) {
				$userProcess[$key] = $item;
			}
		}

		return $userProcess;
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
}
