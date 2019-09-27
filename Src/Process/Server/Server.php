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

use W7\Core\Crontab\Process\CrontabDispatcher;
use W7\Core\Crontab\Process\CrontabExecutor;
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
		$servers = trim(iconfig()->getUserAppConfig('setting')['server']);
		$servers = explode('|', $servers);
		$this->processMap = array_diff($servers, array_intersect(array_keys(ServerEnum::ALL_SERVER), $servers));
		if (($index = array_search(ServerEnum::TYPE_CRONTAB, $this->processMap)) !== false) {
			unset($this->processMap[$index]);
			$this->addCrontabProcess();
		}

		$this->setting['worker_num'] = $this->getWorkerNum();
		return parent::checkSetting();
	}

	private function addCrontabProcess() {
		//追加crontab 的process 到process 的配置中
		$crontabSetting = iconfig()->getUserConfig('crontab')['setting'] ?? [];
		$processConfig = iconfig()->getUserConfig('process');
		$crontabSetting['message_queue_key'] =(int)($crontabSetting['message_queue_key'] ?? 0);
		$crontabSetting['message_queue_key'] = $crontabSetting['message_queue_key'] > 0 ? $crontabSetting['message_queue_key'] : irandom(6, true);

		$processConfig['process']['crontab_dispatch'] = [
			'class' => CrontabDispatcher::class,
			'message_queue_key' => $crontabSetting['message_queue_key'],
			'number' => 1
		];
		$processConfig['process']['crontab_executor'] = [
			'class' => CrontabExecutor::class,
			'message_queue_key' => $crontabSetting['message_queue_key'],
			'number' => $crontabSetting['worker_num'] ?? 1
		];
		iconfig()->setUserConfig('process', $processConfig);

		//追加进程到要启动的进程列表中
		$this->processMap[] = 'crontab_dispatch';
		$this->processMap[] = 'crontab_executor';
	}

	private function getWorkerNum() {
		$workerNum = 0;
		$config = iconfig()->getUserConfig('process');
		$configProcess = $config['process'] ?? [];
		foreach ($this->processMap as $key => $name) {
			if (empty($configProcess[$name])) {
				continue;
			}
			$workerNum += $configProcess[$name]['number'] ?? 1;
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
			$this->pool->registerProcess($name, $configProcess[$name]['class'], $configProcess[$name]['number'] ?? 1);
		}
	}
}
