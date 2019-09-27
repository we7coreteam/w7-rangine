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

use W7\Core\Process\ProcessServerAbstract;
use W7\Core\Server\ServerEnum;

class Server extends ProcessServerAbstract {
	public function __construct() {
		$processConfig = iconfig()->getUserConfig($this->getType());
		$supportServers = iconfig()->getServer();
		$supportServers[$this->getType()] = $processConfig['setting'] ?? [];
		iconfig()->setUserConfig('server', $supportServers);

		parent::__construct();
	}

	public function getType() {
		return ServerEnum::TYPE_PROCESS;
	}

	protected function register() {
		$config = iconfig()->getUserConfig('process');
		$process = $config['ready_start_process'] ?? [];
		$configProcess = $config['process'] ?? [];
		foreach ($process as $key => $name) {
			if (empty($configProcess[$name])) {
				throw new \RuntimeException('process server ' . $name . ' not found as app/Process');
			}
			$this->pool->registerProcess($name, $configProcess[$name]['class'], $configProcess[$name]['number'] ?? 1);
		}
	}
}
