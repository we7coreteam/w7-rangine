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
		$processConfig = iconfig()->getUserConfig('process');
		$supportProcess = $processConfig['process'] ?? [];
		$servers = trim(iconfig()->getUserAppConfig('setting')['server']);
		$servers = explode('|', $servers);

		//获取需要启动的process
		$processMap = array_diff($servers, array_intersect(array_keys(ServerEnum::$ALL_SERVER), $servers));
		//获取不在process配置列表中的process
		$notSupportProcess = array_diff($processMap, array_intersect(array_keys($supportProcess), $processMap));
		if ($notSupportProcess) {
			throw new \RuntimeException('not support ' . implode(', ', $notSupportProcess) . ' process');
		}

		//如果processMap为空，表示输入的指令是bin/server process start
		$startAll = false;
		if (empty($processMap)) {
			$startAll = true;
			$processMap = empty($processMap) ? array_keys($supportProcess) : $processMap;
		}
		foreach ($processMap as $processName) {
			$supportProcess[$processName]['enable'] = $startAll ? ($supportProcess[$processName]['enable'] ?? true) : true;
		}
		$processConfig['process'] = $supportProcess;
		iconfig()->setUserConfig('process', $processConfig);

		$this->setting['worker_num'] = $this->getWorkerNum();
		if ($this->setting['worker_num'] == 0) {
			throw new \RuntimeException('the list of started processes is empty');
		}
		return parent::checkSetting();
	}

	private function getWorkerNum() {
		$workerNum = 0;
		$config = iconfig()->getUserConfig('process');
		$configProcess = $config['process'] ?? [];
		foreach ($configProcess as $key => $process) {
			if (empty($process['enable'])) {
				continue;
			}
			$workerNum += $process['worker_num'] ?? 1;
		}

		return $workerNum;
	}

	protected function register() {
		$config = iconfig()->getUserConfig('process');
		$configProcess = $config['process'] ?? [];
		foreach ($configProcess as $name => $process) {
			if (empty($process['enable'])) {
				continue;
			}
			$this->pool->registerProcess($name, $process['class'], $process['worker_num'] ?? 1);
		}
	}
}
