<?php

namespace W7\Core\Process\Pool;

use Swoole\Process;
use W7\Core\Process\ProcessFactory;

abstract class PoolAbstract {
	/**
	 * @var ProcessFactory
	 */
	protected $processFactory;
	protected $config;
	protected $mqKey = 0;

	public function __construct($config) {
		$this->config = $config;
		$this->mqKey = $this->config['mq_key'] ?? 0;
		$this->processFactory = new ProcessFactory();

		$this->init();
	}

	protected function init() {}

	/**
	 * 保存添加的process
	 * 这里用普通变量保存的原因是 1:worker启动时所有的注册信息已全部保存. 2:worker重新启动时workerid是保持不变的
	 * @param $name
	 * @param $handle
	 * @param $num
	 */
	public function registerProcess($name, $handle, $num) {
		$this->processFactory->add($name, $handle, $num);
	}

	public function getProcessFactory() {
		return $this->processFactory;
	}

	abstract public function start();

	public function get($name, $index = 0) : Process {
		$process = $this->processFactory->getByName($name, $index);
		return $process->getProcess();
	}

	public function stop() {
		return true;
	}
}