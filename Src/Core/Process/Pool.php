<?php

namespace W7\Core\Process;

use Swoole\Process\Pool as PoolManager;
use Swoole\Table;
use W7\Core\Helper\Storage\MemoryTable;

class Pool {
	/**
	 * @var Table
	 */
	private $processMap;

	private $workerNum = 0;
	private $ipcType = 0;
	private $msgqueueKey = 0;
	private $enableCoroutine = false;

	public function __construct($ipcType = 0, $msgqueueKey = 0, $enableCoroutine = false) {
		$this->ipcType = $ipcType;
		$this->msgqueueKey = $msgqueueKey;
		$this->enableCoroutine = $enableCoroutine;

		$this->initProcessMap();

	}

	private function initProcessMap() {
		$memoryTableManager = iloader()->singleton(MemoryTable::class);
		$this->processMap = $memoryTableManager->create('process_pool', 1024, [
				'handle' => [MemoryTable::FIELD_TYPE_STRING, 100],
				'num' => [MemoryTable::FIELD_TYPE_INT, 4],
				'runing_num' => [MemoryTable::FIELD_TYPE_INT, 4]
			]
		);
	}

	public function addProcess($name, $handle, $num = 1) {
		$this->processMap->set($name, [
			'handle' => $handle,
			'num' => $num,
			'runing_num' => 0
		]);

		$this->workerNum += $num;
	}

	public function onWorkerStart() {
		return function (PoolManager $pool, $workerId) {
			$runing = true;
			pcntl_signal(SIGTERM, function () use (&$runing) {
				$runing = false;
			});

			$this->process = $this->getProcess();

			if($this->msgqueueKey) {
//				这里本来可以用process的push直接发消息, 目前有bug
				$this->process->msgqueueKey = $this->msgqueueKey;
				$pool->getProcess()->useQueue($this->msgqueueKey, 2 | \Swoole\Process::IPC_NOWAIT);
			}
			while ($runing) {
				try{
					$this->process->run($pool->getProcess());
				} catch (\Throwable $e) {
					ilogger()->error('run process fail with error ' . $e->getMessage());
				}
			}
		};
	}

	public function onWorkerStop() {
		return function (PoolManager $pool, $workerId) {
			if (empty($this->process)) {
				return false;
			}
			try{
				$this->process->stop($pool->getProcess());
			} catch (\Throwable $e) {
				ilogger()->error('stop process fail with error ' . $e->getMessage());
			} finally {
				$value = $this->processMap->get($this->process->getName());
				--$value['runing_num'];
				$this->processMap->set($this->process->getName(), $value);
			}
		};
	}

	public function onMessage() {
		return function (PoolManager $pool, $message) {
			return $message;
		};
	}

	public function getProcess() : ProcessAbstract {
		foreach ($this->processMap as $name => $item) {
			if ($item['runing_num'] < $item['num']) {
				++$item['runing_num'];
				$this->processMap->set($name, $item);
				return new $item['handle']($name);
			}
		}

		throw new \Exception('create process fail');
	}

	public function start() {
		if ($this->workerNum == 1) {
			throw new \Exception('process num not be zero');
		}

		$manager = new PoolManager($this->workerNum, $this->ipcType, $this->msgqueueKey);
		$manager->on('WorkerStart', $this->onWorkerStart());
		$manager->on('WorkerStop', $this->onWorkerStop());
		$this->ipcType !== 0 && $manager->on('message', $this->onMessage());

		$manager->start();
	}
}