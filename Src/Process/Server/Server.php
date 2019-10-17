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
	private $processMap = [];

	public function __construct() {
		//添加process 到server.php中
		$processConfig = iconfig()->getUserConfig($this->getType());
		$supportServers = iconfig()->getServer();
		$supportServers[$this->getType()] = $processConfig['setting'] ?? [];
		iconfig()->setUserConfig('server', $supportServers);

		parent::__construct();
	}

	public function getType() {
		return ServerEnum::TYPE_PROCESS;
	}

	protected function checkSetting() {
		//获取要启动的process
		$supportProcess = iconfig()->getUserConfig('process')['process'] ?? [];
		$servers = trim(iconfig()->getUserAppConfig('setting')['server']);
		$servers = explode('|', $servers);

		//获取需要启动的process
		$this->processMap = array_diff($servers, array_intersect(array_keys(ServerEnum::$ALL_SERVER), $servers));
		//获取不在process配置列表中的process
		$notSupportProcess = array_diff($this->processMap, array_intersect(array_keys($supportProcess), $this->processMap));
		if ($notSupportProcess) {
			throw new \RuntimeException('not support ' . implode(', ', $notSupportProcess) . ' process');
		}

		$this->setting['worker_num'] = $this->getWorkerNum();
		return parent::checkSetting();
	}

	private function getWorkerNum() {
		$workerNum = 0;
		$config = iconfig()->getUserConfig('process');
		$configProcess = $config['process'] ?? [];
		foreach ($this->processMap as $key => $name) {
			if (empty($configProcess[$name])) {
				continue;
			}
			$workerNum += $configProcess[$name]['worker_num'] ?? 1;
		}

		return $workerNum;
	}

	protected function register() {
		$config = iconfig()->getUserConfig('process');
		$configProcess = $config['process'] ?? [];
		foreach ($this->processMap as $key => $name) {
			if (empty($configProcess[$name])) {
				throw new \RuntimeException('process server ' . $name . ' not found as app/Process');
			}
			$this->pool->registerProcess($name, $configProcess[$name]['class'], $configProcess[$name]['worker_num'] ?? 1);
		}
	}
}
