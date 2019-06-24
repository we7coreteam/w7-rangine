<?php

namespace W7\Core\Crontab\Process;

use Swoole\Process;
use W7\Core\Crontab\CronMap;
use W7\Core\Process\ProcessAbstract;

class CrontabDispatcher extends ProcessAbstract {
	/**
	 * @var CronMap
	 */
	private $cronMap;
	private static $group;

	protected function init() {
		$this->cronMap = new CronMap($this->getCrontabList());
	}

	public static function group($group) {
		static::$group = $group;
	}

	private function getCrontabList() {
		$config = \iconfig()->getUserConfig('crontab');
		$config = $config['list'];

		if (static::$group) {
			$config = $config[static::$group] ?? [];
		} else {
			$tmp = [];
			foreach ($config as $key => $item) {
				$tmp = array_merge($tmp, $item);
			}
			$config = $tmp;
		}

		if (!$config) {
			throw new \Exception('crontab list not be empty');
		}

		return $config;
	}

	public function run(Process $process) {
		if ((ENV & DEBUG) === DEBUG) {
			echo 'Crontab run at ' . date('Y-m-d H:i:s') . PHP_EOL;
		}

		$tasks = $this->cronMap->getRunTasks();
		foreach ($tasks as $name => $task) {
			ilogger()->info('push crontab task ' . $name . ' ' . $task);
			msg_send(msg_get_queue($this->msgqueueKey), 1, $task, false);
		}

		sleep(1);
	}

	public function stop(Process $process) {
		ilogger()->info('crontab dispatcher process exit');
	}
}