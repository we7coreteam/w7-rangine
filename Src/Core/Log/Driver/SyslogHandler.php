<?php
/**
 * @author donknap
 * @date 18-10-18 下午6:22
 */

namespace W7\Core\Log\Driver;


use W7\Core\Log\HandlerInterface;

class SyslogHandler implements HandlerInterface {
	public function getHandler($config) {
		return new \Monolog\Handler\SyslogHandler('w7-', LOG_USER, $config['level']);
	}
}