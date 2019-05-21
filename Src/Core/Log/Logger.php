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
		$logManager = iloader()->get(LogManager::class);
		return $logManager->getChannel($name);
	}

	public function addRecord($level, $message, array $context = array()) {
		//关闭调试模式时，不写入日志
		if (!DEBUG) {
			return true;
		}
		return parent::addRecord($level, $message, $context);
	}
}