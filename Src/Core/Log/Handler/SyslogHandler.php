<?php
/**
 * @author donknap
 * @date 18-10-18 下午6:22
 */

namespace W7\Core\Log\Handler;

use Monolog\Handler\HandlerInterface as MonologInterface;

class SyslogHandler extends \Monolog\Handler\SyslogHandler implements HandlerInterface {
	public static function getHandler($config): MonologInterface {
		return new static('w7-', LOG_USER, $config['level']);
	}
}