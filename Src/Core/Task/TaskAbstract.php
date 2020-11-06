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

namespace W7\Core\Task;

use W7\Contract\Task\TaskInterface;

abstract class TaskAbstract implements TaskInterface {
	public static $connection;

	public static function isAsyncTask() {
		return true;
	}

	public static function shouldQueue() {
		return true;
	}

	public function fail($exception) {
	}

	public function finish($server, $taskId, $data, $params) {
	}
}
