<?php

namespace W7\Core\Crontab\Process;

use W7\Core\Crontab\Container;
use W7\Core\Process\ProcessAbstract;

class CrontabDispatcher extends ProcessAbstract {
	/**
	 * @var Container
	 */
	private $container;
	private static $group;

	protected function init() {
		$this->container = new Container($this->getTasks());
	}

	public static function group($group) {
		static::$group = $group;
	}

	private function getTasks() {
		$config = \iconfig()->getUserConfig('crontab');
		$config = $config['task'][static::$group] ?? [];

		if (!$config) {
			throw new \Exception('crontab task not be empty');
		}

		return $config;
	}

	public function run() {
		if ((ENV & DEBUG) === DEBUG) {
			echo 'Crontab run at ' . date('Y-m-d H:i:s') . PHP_EOL;
		}

		$tasks = $this->container->getRunTasks();
		foreach ($tasks as $name => $task) {
			ilogger()->info('push crontab task ' . $name . ' ' . $task);
			msg_send(msg_get_queue($this->msgqueueKey), 1, $task, false);
		}
	}

	public function stop() {
		ilogger()->info('crontab dispatcher process exit');
	}
}