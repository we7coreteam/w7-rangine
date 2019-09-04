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

class ProcessStartListener extends ListenerAbstract {
	public function run(...$params) {
		list($process, $workerId, $processFactory, $mqKey) = $params;

		$userProcess = $processFactory->make($workerId);
		$userProcess->setProcess($process);
		if (isset($mqKey)) {
			$userProcess->setMq($mqKey);
		}

		//用临时变量保存该进程中的用户进程对象
		App::getApp()->userProcess = $userProcess;

		$userProcess->onStart();
	}
}
