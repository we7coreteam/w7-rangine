<?php
/**
 * @author donknap
 * @date 18-11-22 下午8:26
 */

namespace W7\Core\Process;


use Cron\CronExpression;
use Swoole\Process;
use W7\App;
use W7\Core\Helper\Storage\MemoryTable;
use W7\Core\Message\WorkerMessage;

class CrontabProcess extends ProcessAbstract {
	/**
	 * @var \swoole_table
	 */
	private $table;
	private $config;
	private $cronExpress;
	const NAMEKEY = 'crontab';

	public function __construct() {
		$this->config = \iconfig()->getUserConfig(self::NAMEKEY);
		$this->cronExpress = CronExpression::factory('* * * * *');
	}

	public function check() {
		if (!empty($this->config)) {
			return true;
		} else {
			return false;
		}
	}

	public function run(Process $process) {
		$this->registerTable();
		if ($this->table->count() == 0) {
			$process->exit();
		}
		//最小细度为一分钟
		swoole_timer_tick(1 * 1000, function () {
			$this->sendTask('W7\App\Task\CronTestTask');

//			$task = $this->getRunTask();
//			if (!empty($task)) {
//				foreach ($task as $taskName) {
//					\itask($taskName);
//				}
//			}
		});
	}

	public function read(Process $process, $data) {
		print_r($data);
		return true;
	}

	/**
	 * 获取当前到点要执行的task
	 * 每当获取到下次执行时间后，写入表中，下次直接进行判断
	 */
	private function getRunTask() {
		$timestamp = time();
		$result = [];

		foreach ($this->table as $name => $task) {
			if (empty($task['nextrun'])) {
				try {
					$this->cronExpress->setExpression($task['rule']);
				} catch (\Throwable $e) {
					continue;
				}
				$task['nextrun'] = $this->cronExpress->getNextRunDate()->getTimestamp();
				$this->table->set($name, $task);
			}

			//时间还没到
			if ($task['nextrun'] > $timestamp) {
				continue;
			}

			//时间到了，返回记录，清空表中数据
			if ($task['nextrun'] <= $timestamp) {
				$result[] = $task['task'];
				$task['nextrun'] = 0;
				$this->table->set($name, $task);
			}
		}

		return $result;
	}

	/**
	 * 初始化定时任务到内存表中
	 * 方便管理任务及记录定时器ID
	 *
	 * @return bool
	 */
	private function registerTable() {
		/**
		 * @var MemoryTable $memoryTableManager
		 */
		$memoryTableManager = iloader()->singleton(MemoryTable::class);
		$this->table = $memoryTableManager->create(self::NAMEKEY, count($this->config), [
			'name' => [MemoryTable::FIELD_TYPE_STRING, 30],
			'rule' => [MemoryTable::FIELD_TYPE_STRING, 30],
			'task' => [MemoryTable::FIELD_TYPE_STRING, 50],
			'nextrun' => [MemoryTable::FIELD_TYPE_INT, 4],
		]);
		foreach ($this->config as $name => $setting) {
			$this->table->set($name, [
				'name' => $name,
				'rule' => $setting['rule'],
				'task' => $setting['task'],
				'timer' => 0,
			]);
		}
		return true;
	}

	/**
	 * 自定义进程中没办法调用 $server->task() 方法来发起任务
	 * 此处通过 $server->sendMessage() 将消息发送到 work 进程
	 * 再由 work 进程侦听 pipeMessage 消息来发起任务
	 */
	private function sendTask($taskName) {
		$message = new WorkerMessage();
		$message->operation = WorkerMessage::OPERATION_TASK_ASYNC;
		$message->data = $taskName;
		$message->extra['callback'] = [static::class, 'read'];

		App::$server->getServer()->sendMessage($message->pack(), 0);
	}
}