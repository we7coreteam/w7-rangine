<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
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
