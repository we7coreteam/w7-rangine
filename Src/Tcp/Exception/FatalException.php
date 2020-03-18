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

namespace W7\Tcp\Exception;

use Throwable;
use W7\Core\Exception\FatalExceptionAbstract;
use Whoops\Exception\Inspector;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

class FatalException extends FatalExceptionAbstract {
	public function __construct($message = '', $code = 0, Throwable $previous = null) {
		if ((ENV & BACKTRACE) !== BACKTRACE) {
			$content = 'message: ' . $this->getMessage() . ';    file: ' . $this->getPrevious()->getFile() . ';    line: ' . $this->getPrevious()->getLine();
		} else {
			ob_start();
			$render = new PlainTextHandler();
			$render->setException($this->getPrevious());
			$render->setInspector(new Inspector($this->getPrevious()));
			$render->setRun(new Run());
			$render->handle();
			$content = ob_get_clean();
		}

		parent::__construct($content, $code, $previous);
	}
}
