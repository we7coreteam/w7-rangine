<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Process\Pool;

use W7\Core\Process\ProcessAbstract;
use W7\Core\Process\ProcessFactory;

abstract class PoolAbstract {
	/**
	 * @var ProcessFactory
	 */
	protected $processFactory;
	protected $config;
	protected $mqKey = 0;

	public function __construct(ProcessFactory $processFactory, $config) {
		$this->processFactory = $processFactory;
		$this->config = $config;
		$this->mqKey = (int)($config['message_queue_key'] ?? 0);
		$this->init();
	}

	protected function init() {
	}

	public function registerProcess($name, $handle, $num) {
		/**
		 * @var ProcessAbstract $handleObj
		 */
		$handleObj = new $handle($name, $num);
		if (!$handleObj->check()) {
			return false;
		}

		for ($i = 0; $i < $num; $i++) {
			$process = clone $handleObj;
			$this->processFactory->add($process);
		}
	}

	public function getProcessFactory() {
		return $this->processFactory;
	}

	public function getMqKey() {
		return $this->mqKey;
	}

	public function getProcess($id) {
		return $this->processFactory->getById($id);
	}

	abstract public function start();

	public function stop() {
		return true;
	}

	public function __clone() {
		$this->processFactory = clone $this->processFactory;
	}
}
