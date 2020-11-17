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
	private $processConfig = [];
	private $processMap = [];
	private $processMapWithName = [];

	public function add($name, $handle, $num = 1) {
		for ($i = 0; $i < $num; $i++) {
			$this->processConfig[] = [
				'name' => $name,
				'handle' => $handle,
				'num' => $num
			];
		}
	}

	public function count() {
		return count($this->processConfig);
	}

	public function makeById($id) : ProcessAbstract {
		$value = $this->processConfig[$id];
		$process = new $value['handle']($value['name'], $value['num']);
		$this->processMap[$id] = $process;
		$this->processMapWithName[$value['name']][] = $process;

		return $process;
	}

	public function getById($id) : ProcessAbstract {
		return $this->processMap[$id];
	}

	public function getByName($name, $index = 0) : ProcessAbstract {
		if (empty($this->processMapWithName[$name])) {
			throw new \Exception('the process ' . $name . ' not exist');
		}
		if (empty($this->processMapWithName[$name][$index])) {
			throw new \Exception('the process ' . $name . '[' . $index . '] not exist');
		}

		return $this->processMapWithName[$name][$index];
	}

	public function delById($id) {
		unset($this->processConfig[$id]);
		unset($this->processMap[$id]);
	}
}
