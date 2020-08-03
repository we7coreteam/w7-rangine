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

namespace W7\Core\Task;

use W7\App;
use W7\Core\Facades\Container;
use W7\Core\Facades\Context;

abstract class TaskAbstract implements TaskInterface {
	public $options = [];

	public function __construct($options = []) {
		$this->options = $options;
	}

	final public function handle() {
		return $this->run(App::$server, Context::getCoroutineId(), Container::get('worker_id'), $this->options);
	}

	public function finish($server, $taskId, $data, $params) {
	}
}
