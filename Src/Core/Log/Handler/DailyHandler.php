<?php
/**
 * @author donknap
 * @date 18-10-18 下午6:15
 */

namespace W7\Core\Log\Handler;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface as MonologInterface;
use Monolog\Handler\RotatingFileHandler;

class DailyHandler extends RotatingFileHandler implements HandlerInterface {
	const SIMPLE_FORMAT = "[%datetime%] [workid:%workid% coid:%coid%] %channel%.%level_name%: %message% %context% %extra%\n\n";

	public static function getHandler($config): MonologInterface {
		$handler = new static($config['path'], $config['days'], $config['level']);
		$formatter = new LineFormatter(self::SIMPLE_FORMAT);
		$formatter->includeStacktraces(true);
		$handler->setFormatter($formatter);
		return $handler;
	}

	protected function streamWrite($stream, array $record) {
		if (isCo()) {
			go(function() use ($stream, $record) {
				@parent::streamWrite($stream, $record);
			});
		} else {
			@parent::streamWrite($stream, $record);
		}

	}
}