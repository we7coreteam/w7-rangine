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

namespace W7\Core\Helper\Compate;

use W7\Core\Exception\HandlerExceptions;

class FpmCoroutine {
	private array $generatorMap = [];

	public function __construct() {
		register_shutdown_function(function () {
			$e = error_get_last();
			if (!$e || HandlerExceptions::isIgnoreErrorTypes($e['type'])) {
				$this->run();
			}
		});
	}

	public function add(\Generator $generator): void {
		$this->generatorMap[] = $generator;
	}

	public function run(): void {
		/**
		 * @var \Generator $generator
		 */
		foreach ($this->generatorMap as $generator) {
			$result = $generator->current();
			$generator->send($result);

			if (!$generator->valid()) {
				//表示该生成器结束，已关闭
			} else {
				$this->add($generator);
			}
		}
	}
}
