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

namespace W7\Core\Log;

use Monolog\Handler\BufferHandler;

/**
 * Class Logger
 * @package W7\Core\Log
 *
 * @property $bufferLimit
 */
class Logger extends \Monolog\Logger {
	public function addRecord($level, $message, array $context = array()) {
		$result =  parent::addRecord($level, $message, $context);

		if ($this->bufferLimit == 1) {
			$this->flushLog();
		}
		return $result;
	}

	private function flushLog() {
		/**
		 * @var BufferHandler $handler
		 */
		foreach ($this->getHandlers() as $handler) {
			$handler->flush();
		}
	}

	public function __destruct() {
		$this->flushLog();
	}
}
