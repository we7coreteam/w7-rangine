<?php
/**
 * @author donknap
 * @date 18-10-18 下午4:25
 */

namespace W7\Core\Log\Driver;

use Monolog\Formatter\LineFormatter;
use W7\Core\Log\HandlerInterface;

class StreamHandler extends \Monolog\Handler\StreamHandler implements HandlerInterface {
	const SIMPLE_FORMAT = "[%datetime%] [workid:%workid% co/task:%coid%] %channel%.%level_name%: %message% %context% %extra%\n\n";

	static public function getHandler($config) {
		$handler = new static($config['path'], $config['level']);
		$formatter = new LineFormatter(self::SIMPLE_FORMAT);
		$formatter->includeStacktraces(true);
		$handler->setFormatter($formatter);
		return $handler;
	}

	protected function write(array $record) {
		go(function() use ($record) {
			parent::write($record);
		});
	}
}