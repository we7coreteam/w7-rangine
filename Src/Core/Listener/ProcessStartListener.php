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

namespace W7\Core\Listener;

use W7\App;
use W7\Core\Facades\Config;
use W7\Core\Process\ProcessAbstract;
use W7\Core\Process\ProcessFactory;

class ProcessStartListener extends ListenerAbstract {
	public function run(...$params) {
		/**
		 * @var ProcessFactory $processFactory
		 */
		list($serverType, $process, $workerId, $processFactory, $mqKey) = $params;
		//重新播种随机因子
		mt_srand();

		/**
		 * @var ProcessAbstract $processInstance
		 */
		$processInstance = $processFactory->makeById($workerId);
		$processInstance->setProcess($process);
		$processInstance->setServerType($serverType);
		$processInstance->setWorkerId($workerId);
		$name = $processInstance->getName();

		$mqKey = Config::get("process.process.$name.message_queue_key", $mqKey);
		$mqKey = (int)$mqKey;
		$processInstance->setMq($mqKey);

		isetProcessTitle($processInstance->getProcessName());

		//用临时变量保存该进程中的用户进程对象
		App::getApp()->process = $processInstance;

		$processInstance->onStart();
	}
}
