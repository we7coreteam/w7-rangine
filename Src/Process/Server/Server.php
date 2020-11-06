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
		$processSetting = $this->getConfig()->get($this->getType() . '.setting', []);
		$this->getConfig()->set('server.' . $this->getType(), $processSetting);

		parent::__construct();
	}

	public function getType() {
		return ServerEnum::TYPE_PROCESS;
	}

	protected function checkSetting() {
		//获取要启动的process
		$supportProcess = Config::get('process.process', []);
		$servers = trim(Config::get('app.setting.server'));
		$servers = explode('|', $servers);

		//获取需要启动的process
		$processMap = array_diff($servers, array_intersect(array_keys(ServerEnum::$ALL_SERVER), $servers));
		//获取不在process配置列表中的process
		$notSupportProcess = array_diff($processMap, array_intersect(array_keys($supportProcess), $processMap));
		if ($notSupportProcess) {
			throw new \RuntimeException('process ' . implode(', ', $notSupportProcess) . ' not exist, please check the configuration in config/process.php');
		}

		//如果processMap为空，表示输入的指令是bin/server process start，将启动所有enable的process
		$startAll = false;
		if (empty($processMap)) {
			$startAll = true;
			$processMap = empty($processMap) ? array_keys($supportProcess) : $processMap;
		}
		//设置要启动的process的enable属性为true
		foreach ($processMap as $processName) {
			//如果是全部启动的话，enable和配置中的值保持一致
			$supportProcess[$processName]['enable'] = $startAll ? ($supportProcess[$processName]['enable'] ?? true) : true;
		}
		Config::set('process.process', $supportProcess);

		$this->setting['worker_num'] = $this->getWorkerNum();
		if ($this->setting['worker_num'] == 0) {
			throw new \RuntimeException('the list of started processes is empty, please check the configuration in config/process.php');
		}

		return parent::checkSetting();
	}

	private function getWorkerNum() {
		$workerNum = 0;
		$configProcess = Config::get('process.process', []);
		foreach ($configProcess as $key => $process) {
			if (empty($process['enable'])) {
				continue;
			}
			$workerNum += $process['worker_num'] ?? 1;
		}

		return $workerNum;
	}

	protected function register() {
		$configProcess = Config::get('process.process', []);
		foreach ($configProcess as $name => $process) {
			if (empty($process['enable'])) {
				continue;
			}
			$this->pool->registerProcess($name, $process['class'], $process['worker_num'] ?? 1);
		}
	}
}
