<?php

namespace W7\Core\Helper\Compate;

use W7\Core\Exception\HandlerExceptions;

class Coroutine {
	private static $generatorMap = [];
	private static $hasRegisterTrigger;

	public function __construct() {
		if (!self::$hasRegisterTrigger) {
			register_shutdown_function(function () {
				$e = error_get_last();
				if (!$e || HandlerExceptions::isIgnoreErrorTypes($e['type'])) {
					$this->run();
				}
			});
			self::$hasRegisterTrigger = true;
		}
	}

	public function add(\Generator $generator) {
		self::$generatorMap[] = $generator;
	}

	public function run() {
		/**
		 * @var \Generator $generator
		 */
		foreach (self::$generatorMap as $generator) {
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