<?php
/**
 * @author donknap
 * @date 18-10-18 下午7:31
 */

namespace W7\Core\Log;

class Logger extends \Monolog\Logger {
	/**
	 * @param $name
	 * @return \Monolog\Logger
	 */
	public function channel($name) {
		/**
		 * @var LogManager $logManager
		 */
		$logManager = iloader()->singleton(LogManager::class);
		return $logManager->getChannel($name);
	}

	public function addRecord($level, $message, array $context = array()) {
		$result =  parent::addRecord($level, $message, $context);

		if ($this->bufferLimit == 1) {
			$this->flushLog();
		}
		return $result;
	}

	private function flushLog() {
		foreach ($this->getHandlers() as $handler) {
			$handler->flush();
		}
	}

	public function __destruct() {
		$this->flushLog();
	}
}