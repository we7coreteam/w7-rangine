<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Exception;

class ExceptionHandle {
	private $type;

	public function __construct($type) {
		$this->type = ucfirst($type);
	}

	public function log(\Throwable $throwable) {
		$errorMessage = sprintf(
			'Uncaught Exception %s: "%s" at %s line %s',
			get_class($throwable),
			$throwable->getMessage(),
			$throwable->getFile(),
			$throwable->getLine()
		);

		$context = [];
		if ((ENV & BACKTRACE) === BACKTRACE) {
			$context = array('exception' => $throwable);
		}

		ilogger()->error($errorMessage, $context);
	}

	public function handle(\Throwable $throwable) {
		$previous = $throwable;
		if (!($throwable instanceof ResponseExceptionAbstract)) {
			$class = 'W7\Core\Exception\\' . $this->type . 'FatalException';
			$throwable = new $class($throwable->getMessage(), $throwable->getCode(), $throwable);
		}

		if ($throwable->isLoggable) {
			$this->log($previous);
		}
		return $throwable->render();
	}
}
