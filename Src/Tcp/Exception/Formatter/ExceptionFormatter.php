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

namespace W7\Tcp\Exception\Formatter;

use W7\Http\Exception\Formatter\ExceptionFormatter as ExceptionFormatterAbstract;
use Whoops\Exception\Inspector;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

class ExceptionFormatter extends ExceptionFormatterAbstract {
	public function formatDevelopmentExceptionToString(\Throwable $e): string {
		$previous = $e->getPrevious() ?? $e;

		if ((ENV & BACKTRACE) !== BACKTRACE) {
			$content = 'message: ' . $e->getMessage() . ';    file: ' . $previous->getFile() . ';    line: ' . $previous->getLine();
		} else {
			ob_start();
			$render = new PlainTextHandler();
			$render->setException($previous);
			$render->setInspector(new Inspector($previous));
			$render->setRun(new Run());
			$render->handle();
			$content = ob_get_clean();
		}

		return $content;
	}
}
