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

namespace W7\Core\Process;

class ProcessFactory {
	private $processMap = [];

	public function add(ProcessAbstract $process) {
		$this->processMap[] = $process;
	}

	public function count() {
		return count($this->processMap);
	}

	public function has($id) {
		return !empty($this->processMap[$id]);
	}

	public function getById($id) : ProcessAbstract {
		return $this->processMap[$id];
	}

	public function delById($id) {
		unset($this->processMap[$id]);
	}
}
