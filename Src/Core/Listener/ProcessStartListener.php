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
use W7\Core\Process\ProcessAbstract;

class ProcessStartListener extends ListenerAbstract {
	public function run(...$params) {
		list($processInstance, $workerId, $options) = $params;
		mt_srand();

		/**
		 * @var ProcessAbstract $processInstance
		 */
		$processInstance->setWorkerId($workerId);
		$name = $processInstance->getName();

		$mqKey = $options['message_queue_key'] ?? 0;
		$mqKey = $this->getConfig()->get("process.process.$name.message_queue_key", $mqKey);
		$mqKey = (int)$mqKey;
		$processInstance->setMq($mqKey);

		isetProcessTitle($processInstance->getProcessName());

		//A temporary variable is used to hold the user process object in the process
		App::getApp()->process = $processInstance;

		$processInstance->onStart();
	}
}
