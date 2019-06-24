<?php
/**
 * @author donknap
 * @date 18-11-22 下午8:27
 */

namespace W7\Core\Process;

use Swoole\Process;

abstract class ProcessAbstract {
	protected $name;

	public function __construct($name) {
		$this->name = $name;
		$this->init();
	}

	protected function init() {

	}

	public function getName() {
		return $this->name;
	}

	abstract public function run(Process $process);

	public function stop(Process $process) {

	}
}