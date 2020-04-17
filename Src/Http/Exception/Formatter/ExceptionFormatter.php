<?php

namespace W7\Http\Exception\Formatter;

use W7\Core\Exception\Formatter\ExceptionFormatterInterface;
use Whoops\Exception\Inspector;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ExceptionFormatter implements ExceptionFormatterInterface {
	public function formatDevelopmentException(\Throwable $e): string {
		$previous = !empty($e->getPrevious()) ? $e->getPrevious() : $e;

		if ((ENV & BACKTRACE) !== BACKTRACE) {
			$content = 'message: ' . $e->getMessage() . '<br/>file: ' . $previous->getFile() . '<br/>line: ' . $previous->getLine();
		} else {
			ob_start();
			$render = new PrettyPageHandler();
			$render->handleUnconditionally(true);
			$render->setException($previous);
			$render->setInspector(new Inspector($previous));
			$render->setRun(new Run());
			$render->handle();
			$content = ob_get_clean();
		}

		return $content;
	}

	public function formatReleaseException(\Throwable $e): string {
		return \json_encode(['error' => '系统内部错误']);
	}
}