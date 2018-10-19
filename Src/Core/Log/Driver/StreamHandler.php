<?php
/**
 * @author donknap
 * @date 18-10-18 下午4:25
 */

namespace W7\Core\Log\Driver;

use Monolog\Formatter\LineFormatter;
use W7\Core\Log\HandlerInterface;

class StreamHandler implements HandlerInterface {
	public function getHandler($config) {
		$handler = new \Monolog\Handler\StreamHandler($config['path'], $config['level']);
		$formatter = new LineFormatter();
		$formatter->includeStacktraces(true);
		$handler->setFormatter($formatter);
		return $handler;
	}
}