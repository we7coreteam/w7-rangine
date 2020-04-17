<?php

namespace W7\Tcp\Exception\Formatter;

use W7\Http\Exception\Formatter\ExceptionFormatter as ExceptionFormatterAbstract;
use Whoops\Exception\Inspector;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

class ExceptionFormatter extends ExceptionFormatterAbstract{
	public function formatDevelopmentException(\Throwable $e): string {
		$previous = !empty($e->getPrevious()) ? $e->getPrevious() : $e;

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