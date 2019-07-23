<?php

namespace W7\Core\Listener;

use W7\App;

class ProcessStopListener extends ListenerAbstract {
	public function run(...$params) {
		$userProcess = App::getApp()->userProcess;
		if (empty($userProcess)) {
			return false;
		}

		try{
			$userProcess->onStop();
		} catch (\Throwable $e) {
			ilogger()->error('stop process fail with error ' . $e->getMessage());
		}
	}
}