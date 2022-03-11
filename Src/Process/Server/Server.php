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

use RuntimeException;
use W7\Core\Process\ProcessServerAbstract;
use W7\Core\Server\ServerEnum;

class Server extends ProcessServerAbstract {
	public function __construct() {
		$processSetting = $this->getConfig()->get($this->getType() . '.setting', []);
		$this->getConfig()->set('server.' . $this->getType(), $processSetting);

		parent::__construct();
	}

	public function getType() {
		return ServerEnum::TYPE_PROCESS;
	}

	protected function checkSetting() {
		$supportProcess = $this->getConfig()->get('process.process', []);
		$servers = trim($this->getConfig()->get('app.setting.server'));
		$servers = explode('|', $servers);

		$processMap = array_diff($servers, (array)array_intersect(array_keys(ServerEnum::$ALL_SERVER), $servers));
		$notSupportProcess = array_diff((array)$processMap, array_intersect(array_keys($supportProcess), (array)$processMap));
		if ($notSupportProcess) {
			throw new \RuntimeException('process ' . implode(', ', $notSupportProcess) . ' not exist, please check the configuration in config/process.php');
		}

		$startAll = false;
		if (empty($processMap)) {
			$startAll = true;
			$processMap = empty($processMap) ? array_keys($supportProcess) : $processMap;
		}
		foreach ($processMap as $processName) {
			$supportProcess[$processName]['enable'] = $startAll ? ($supportProcess[$processName]['enable'] ?? true) : true;
		}
		$this->getConfig()->set('process.process', $supportProcess);

		$this->setting['worker_num'] = $this->getWorkerNum();
		if ($this->setting['worker_num'] == 0) {
			throw new \RuntimeException('the list of started processes is empty, please check the configuration in config/process.php');
		}

		parent::checkSetting();
	}

	private function getWorkerNum() {
		$workerNum = 0;
		$configProcess = $this->getConfig()->get('process.process', []);
		foreach ($configProcess as $process) {
			if (empty($process['enable'])) {
				continue;
			}
			$workerNum += $process['worker_num'] ?? 1;
		}

		return $workerNum;
	}

	protected function register() {
		$configProcess = $this->getConfig()->get('process.process', []);
		foreach ($configProcess as $name => $process) {
			if (empty($process['enable'])) {
				continue;
			}
			$this->pool->registerProcess($name, $process['class'], $process['worker_num'] ?? 1);
		}

		if ($this->pool->getProcessFactory()->count() == 0) {
			throw new RuntimeException('The process does not exist. Please confirm in config/process.php');
		}
	}
}
