<?php

namespace W7\Core\Process\Pool;

class PoolServiceAbstract {
	/**
	 * @var PoolAbstract
	 */
	protected $processPool;
	protected $config;
	protected $poolConfig;
	protected static $group = 'default';

	public static function group($group) {
		static::$group = $group;
	}

	public function registerPool($class) {
		$this->processPool = new $class($this->poolConfig);
		if (!($this->processPool instanceof PoolAbstract)) {
			throw new \Exception('the pool must be instance PoolAbstract');
		}

		return $this;
	}

	public function start() {}

	public function stop() {
		$this->processPool->stop();
	}
}