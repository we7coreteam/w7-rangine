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

namespace W7\Core\Log\Handler;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface as MonologInterface;
use Monolog\Handler\RotatingFileHandler;

class DailyHandler extends RotatingFileHandler implements HandlerInterface {
	const SIMPLE_FORMAT = "[%datetime%] [workid:%workid% coid:%coid%] %channel%.%level_name%: %message% %context% %extra%\n\n";

	public static function getHandler($config): MonologInterface {
		$handler = new static($config['path'], $config['days'], $config['level']);
		$formatter = new LineFormatter(empty($config['format']) ? self::SIMPLE_FORMAT : $config['format']);
		$formatter->includeStacktraces(true);
		$handler->setFormatter($formatter);
		return $handler;
	}

	protected function streamWrite($stream, array $record) {
		if (isCo()) {
			go(function () use ($stream, $record) {
				@parent::streamWrite($stream, $record);
			});
		} else {
			@parent::streamWrite($stream, $record);
		}
	}
}
