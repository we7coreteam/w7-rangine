<?php
/**
 * @author donknap
 * @date 18-10-18 下午6:15
 */

namespace W7\Core\Log\Driver;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use W7\Core\Log\HandlerInterface;

class DailyHandler implements HandlerInterface {
	public function getHandler($config) {
		$handler = new RotatingFileHandler($config['path'], $config['days'], $config['level']);

		$formatter = new LineFormatter();
		$formatter->includeStacktraces(true);
		$handler->setFormatter($formatter);
		return $handler;
	}
}