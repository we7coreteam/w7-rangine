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

class StreamHandler extends \Monolog\Handler\StreamHandler implements HandlerInterface {
	const SIMPLE_FORMAT = "[%datetime%] [workid:%workid% co/task:%coid%] %channel%.%level_name%: %message% %context% %extra%\n\n";

	public static function getHandler($config): MonologInterface {
		$handler = new static($config['path'], $config['level']);
		$formatter = new LineFormatter(empty($config['format']) ? self::SIMPLE_FORMAT : $config['format']);
		$formatter->includeStacktraces(true);
		$handler->setFormatter($formatter);
		return $handler;
	}

	public function handleBatch(array $records) {
		$this->write($records);
	}

	protected function streamWrite($stream, array $record) {
		$record = array_column($record, 'formatted');
		$record = ['formatted' => implode("\n", $record) . "\n"];
		if (isCo()) {
			go(function () use ($stream, $record) {
				@parent::streamWrite($stream, $record);
			});
		} else {
			@parent::streamWrite($stream, $record);
		}
	}

	public function preProcess($record) : array {
		$record['formatted'] = $this->getFormatter()->format($record);
		return $record;
	}
}
