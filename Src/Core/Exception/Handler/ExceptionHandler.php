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

namespace W7\Core\Exception\Handler;

use Psr\Http\Message\ResponseInterface;
use W7\Core\Exception\FatalExceptionAbstract;
use W7\Core\Exception\ResponseExceptionAbstract;

class ExceptionHandler extends HandlerAbstract {
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

	public function handle(ResponseExceptionAbstract $e) : ResponseInterface {
		if ($e->isLoggable) {
			if ($e instanceof FatalExceptionAbstract) {
				$e = $e->getPrevious();
			}
			$this->log($e);
		}
		return $e->render();
	}
}
