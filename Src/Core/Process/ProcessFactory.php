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
	private $processNames = [];
	private $processIds = [];

	public function add($name, $handle, $num = 1) {
		for ($i = 0; $i < $num; $i++) {
			$this->processMap[] = [
				'name' => $name,
				'handle' => $handle,
				'num' => $num
			];
		}
	}

	public function count() {
		return count($this->processMap);
	}

	public function make($id) : ProcessAbstract {
		$value = $this->processMap[$id];
		$process = new $value['handle']($value['name'], $value['num']);
		$this->processIds[$id] = $process;
		$this->processNames[$value['name']][] = $process;

		return $process;
	}

	public function del($name) {
		unset($this->processMap[$name]);
	}

	public function get($id) : ProcessAbstract {
		return $this->processIds[$id];
	}

	public function getByName($name, $index = 0) : ProcessAbstract {
		if (empty($this->processNames[$name])) {
			throw new \Exception('the process ' . $name . ' not exist');
		}
		if (empty($this->processNames[$name][$index])) {
			throw new \Exception('the process ' . $name . '[' . $index . '] not exist');
		}

		return $this->processNames[$name][$index];
	}
}
