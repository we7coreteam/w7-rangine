<?php
/**
 * @author donknap
 * @date 18-11-22 下午8:26
 */

namespace W7\Core\Process;


use Swoole\Process;
use W7\Core\Helper\Storage\MemoryTable;

class CrontabProcess extends ProcessAbstract {
	private $table;

	public function __construct() {
		/**
		 * @var MemoryTable $memoryManger
		 */
		$memoryManger = iloader()->singleton(MemoryTable::class);
		$this->table = $memoryManger->create('crontab');
	}

	public function check() {
		return true;
		if ($this->table->count() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function run(Process $process) {

	}

	public function read(Process $process, $data) {
		print_r($data);
		return true;
	}

	/**
	 * 根据规则注册定时器
	 */
	private function registerCron() {

	}
}