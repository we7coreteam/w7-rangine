<?php

namespace W7\Core\Listener;

use W7\App;

class ProcessStartListener extends ListenerAbstract {
	public function run(...$params) {
		list($process, $workerId, $processFactory, $mqKey) = $params;

		$userProcess = $processFactory->make($workerId);
		$userProcess->setProcess($process);
		if($mqKey) {
			$userProcess->setMq($mqKey);
		}

		//用临时变量保存该进程中的用户进程对象
		App::getApp()->userProcess = $userProcess;

		$userProcess->onStart();
	}
}