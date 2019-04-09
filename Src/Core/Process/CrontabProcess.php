<?php
/**
 * @author donknap
 * @date 18-11-22 下午8:26
 */

namespace W7\Core\Process;


use Cron\CronExpression;
use Swoole\Process;
use W7\App;
use W7\Core\Dispatcher\ProcessDispather;
use W7\Core\Helper\Storage\MemoryTable;
use W7\Core\Message\CrontabMessage;
use W7\Core\Message\Message;
use W7\Core\Message\TaskMessage;

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
		$this->setting = \iconfig()->getUserAppConfig('crontab');

		$this->setting['interval'] = intval($this->setting['interval']);
		if (empty($this->setting['interval'])) {
			$this->setting['interval'] = 1 * 1000;
		} else {
			$this->setting['interval'] *= 1000;
		}

		$this->development = \iconfig()->getUserAppConfig('setting')['development'] ?? 0;
	}

	public function check() {
		if (isset($this->setting['enabled']) && empty($this->setting['enabled'])) {
			return false;
		}

		if (!empty($this->config)) {
			return true;
		} else {
			return false;
		}
	}

	public function run(Process $process) {
		if (isset($this->setting['enabled']) && empty($this->setting['enabled'])) {
			$process->exit();
		}

		$this->registerTable();
		if ($this->table->count() == 0) {
			$process->exit();
		}
		//最小细度为一分钟
		swoole_timer_tick($this->setting['interval'], function () {
			if (!empty($this->development)) {
				echo 'Crontab run at ' . date('Y-m-d H:i:s') . PHP_EOL;
			}

			$task = $this->getRunTask();

			if (!empty($this->development)) {
				$result = [];
				foreach ($this->table as $name1 => $row1) {
					$row1['nextruntime'] = date('Y-m-d H:i:s', $row1['nextrun']);
					$result[] = $row1;
				}
				ilogger()->info('Crontab task ' . idd($result));
			}

			if (!empty($task)) {
				foreach ($task as $name => $taskName) {
					$this->sendTask($name, $taskName);
				}
			}

		});
	}

	public function read(Process $process, $data) {
		$message = Message::unpack($data);

		$taskData = $this->table->get($message->name);
		$taskData['isrun'] = 0;
		$this->table->set($message->name, $taskData);

		$result = [];
		foreach ($this->table as $name => $task) {
			$result[] = $task;
		}
		ilogger()->info('这里是read方法' .  $message->name . idd($result));
		return true;
	}

	/**
	 * 任务执行完成后，标记状态
	 * 因为此函数是在 onFinish 事件中调用到，此事件和当前进程不在同一个进程内
	 * 所以需要需要通道发送数据到此进程的 read 函数中处理
	 * 此方法逻辑上不应该在这里，但是为了方便代码维护，放到这里
	 */
	public function finishTask($server, $taskId, $result, $params) {
		ilogger()->info($params['cronTask'] . ' finished');
		/**
		 * @var ProcessDispather $processManager
		 */
		$processManager = iloader()->singleton(ProcessDispather::class);
		$message = new CrontabMessage();
		$message->name = $params['cronTask'];
		$processManager->write(CrontabProcess::class, $message->pack());
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
			//如果当前任务还在执行中，则不返回任务，直接清空，跳过此次执行
			if ($task['nextrun'] <= $timestamp) {
				$result[$name] = $task['task'];
				$task['nextrun'] = 0;
				$this->table->set($name, $task);

				if (!empty($task['isrun'])) {
					unset($result[$name]);
				}
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
			'isrun' => [MemoryTable::FIELD_TYPE_INT, 4],
		]);
		foreach ($this->config as $name => $setting) {
			$this->table->set($name, [
				'name' => $name,
				'rule' => $setting['rule'],
				'task' => $setting['task'],
				'timer' => 0,
				'isrun' => 0
			]);
		}

		return true;
	}

	/**
	 * 自定义进程中没办法调用 $server->task() 方法来发起任务
	 * 此处通过 $server->sendMessage() 将消息发送到 work 进程
	 * 再由 work 进程侦听 pipeMessage 消息来发起任务
	 * 发送任务时会将执行的任务标记为1，执行完调用 OnFinish 回调时，再变更状态
	 */
	private function sendTask($cronTask, $taskName) {
		$taskMessage = new TaskMessage();
		$taskMessage->type = TaskMessage::OPERATION_TASK_ASYNC;
		$taskMessage->task = $taskName;
		$taskMessage->params['cronTask'] = $cronTask;
		$taskMessage->setFinishCallback(static::class, 'finishTask');

		if (App::$server->getServer()->sendMessage($taskMessage->pack(), 0)) {
			$taskData = $this->table->get($cronTask);
			$taskData['isrun'] = 1;
			$this->table->set($cronTask, $taskData);
		}
	}
}