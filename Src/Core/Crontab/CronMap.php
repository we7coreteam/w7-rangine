<?php

namespace W7\Core\Crontab;

use W7\Core\Helper\Storage\MemoryTable;

class CronMap{
	/**
	 * @var \swoole_table
	 */
	private $storage;
	private $tasks;

	public function __construct($config) {
		$this->registerStorage(count($config));
		foreach ($config as $name => $task) {
			$this->add($name, $task);
		}
	}

	private function registerStorage($size) {
		$memoryTableManager = iloader()->singleton(MemoryTable::class);
		$this->storage = $memoryTableManager->create('crontab', $size, [
				'isrun' => [MemoryTable::FIELD_TYPE_INT, 4],
			]
		);
	}

	public function add($name, $config){
		$this->tasks[$name] = new CrontabTask($name, $config);
	}

	public function rm($name) {
		unset($this->tasks[$name]);
	}

	public function count() {
		return count($this->tasks);
	}

	public function runTask($name) {
		$this->storage->set($name, ['isrun' => 1]);
		$this->tasks[$name]->run = true;
	}

	public function finishTask($name) {
		$this->storage->del($name);
		$this->tasks[$name]->run = false;
	}

	public function getRunTasks() {
		$time = time();

		$tasks = [];
		foreach ($this->tasks as $task) {
			if (empty($this->storage->get($task->getName(), 'isrun')) && $task->check($time)) {
				$tasks[$task->getName()] = $task->getTask();
			}
		}

		return $tasks;
	}
}