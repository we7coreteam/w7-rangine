<?php
/**
 * @author donknap
 * @date 18-10-18 下午6:26
 */

namespace W7\Core\Log\driver;


use W7\Core\Log\HandlerInterface;

class ErrorlogHandler implements HandlerInterface {
	public function getHanlder($config) {
		return new \Monolog\Handler\ErrorLogHandler();
	}
}