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

namespace W7\Crontab\Process;

use W7\Crontab\Task\TaskManager;
use W7\Core\Process\ProcessAbstract;

class CrontabDispatcher extends ProcessAbstract {
	/**
	 * @var TaskManager
	 */
	private $taskManager;
	private static $tasks = [];

	protected function init() {
		$this->taskManager = new TaskManager(static::getTasks());
	}

	public static function getTasks() {
		if (!static::$tasks) {
			$tasks = \iconfig()->getUserConfig('crontab');
			foreach ($tasks as $name => $task) {
				if (isset($task['enable']) && $task['enable'] === false) {
					continue;
				}
				static::$tasks[$name] = $task;
			}
		}
		return static::$tasks;
	}

	public function run() {
		if ((ENV & DEBUG) === DEBUG) {
			echo 'Crontab run at ' . date('Y-m-d H:i:s') . PHP_EOL;
		}

		$tasks = $this->taskManager->getRunTasks();
		foreach ($tasks as $name => $task) {
			ilogger()->info('push crontab task ' . $name . ' ' . $task);
			msg_send(msg_get_queue($this->mqKey), 1, $task, false);
		}
	}
}
