<?php

namespace W7\Core\Process;

class ProcessFactory {
	private $processMap;

	public function add($name, $handle, $num = 1) {
		for($i = 0; $i < $num; $i++) {
			$this->processMap[] = [
				'name' => $name,
				'handle' => $handle,
				'num' => $num,
				'runing_num' => 0
			];
		}
	}

	public function del($name) {
		unset($this->processMap[$name]);
	}

	public function count() {
		return count($this->processMap);
	}

	public function make($id) : ProcessAbstract {
		$value = $this->processMap[$id];
		return new $value['handle']($value['name'], $value['num']);
	}
}