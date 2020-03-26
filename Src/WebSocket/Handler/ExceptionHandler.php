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

namespace W7\WebSocket\Handler;

use W7\Core\Exception\Handler\HandlerAbstract;
use W7\Http\Message\Server\Response;
use Whoops\Exception\Inspector;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

class ExceptionHandler extends HandlerAbstract {
	protected function handleDevelopment(\Throwable $e): Response {
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

		return $this->getResponse()->withStatus(500)->withContent($content);
	}
}
