<?php

namespace W7\Core\Crontab\Process;

use W7\Core\Crontab\Task\TaskManager;
use W7\Core\Process\ProcessAbstract;

class CrontabDispatcher extends ProcessAbstract {
	/**
	 * @var TaskManager
	 */
	private $taskManager;
	static private $tasks = [];

	protected function init() {
		$this->taskManager = new TaskManager(static::getTasks());
	}

	/**
	 * 指定要启动的task  按,隔开 testTask,test1Task
	 * @param $tasks
	 * @throws \Exception
	 */
	public static function setTasks($tasks) {
		$tasks = explode(',', $tasks);
		$configTasks = \iconfig()->getUserConfig('crontab')['task'];
		foreach ($tasks as $key => $task) {
			if (empty($configTasks[$task])) {
				throw new \Exception('the task ' . $task . ' does not exist');
			}
			static::$tasks[] = $configTasks[$task];
		}
	}

	public static function getTasks() {
		if (!static::$tasks) {
			static::$tasks = \iconfig()->getUserConfig('crontab')['task'];
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