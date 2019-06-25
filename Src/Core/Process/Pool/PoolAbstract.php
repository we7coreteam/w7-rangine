<?php

namespace W7\Core\Process\Pool;

abstract class PoolAbstract {
	/**
	 * @var Container
	 */
	protected $container;
	protected $config;

	public function __construct($config) {
		$this->config = $config;
		$this->container = iloader()->singleton(Container::class);

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
	public function addProcess($name, $handle, $num) {
		$this->container->add($name, $handle, $num);
	}

	abstract public function start();
}