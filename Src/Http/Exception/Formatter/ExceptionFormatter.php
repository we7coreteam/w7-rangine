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

namespace W7\Http\Exception\Formatter;

use W7\Core\Exception\Formatter\ExceptionFormatterInterface;
use Whoops\Exception\Inspector;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ExceptionFormatter implements ExceptionFormatterInterface {
	public function formatDevelopmentExceptionToString(\Throwable $e): string {
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

	public function formatReleaseExceptionToString(\Throwable $e): string {
		return \json_encode(['error' => '系统内部错误'], JSON_UNESCAPED_UNICODE);
	}
}
