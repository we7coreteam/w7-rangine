<?php

namespace W7\Core\Crontab\Process;

use W7\Core\Crontab\Task\TaskManager;
use W7\Core\Process\ProcessAbstract;

class CrontabDispatcher extends ProcessAbstract {
	/**
	 * @var TaskManager
	 */
	private $taskManager;
	static $group = 'default';

	protected function init() {
		$this->taskManager = new TaskManager($this->getTasks());
	}

	public static function group($group) {
		static::$group = $group;
	}

	private function getTasks() {
		$config = \iconfig()->getUserConfig('crontab');
		return $config['task'][static::$group];
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