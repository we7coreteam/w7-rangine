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

namespace W7\Crontab\Exception;

use Psr\Http\Message\ResponseInterface;
use W7\Core\Exception\FatalExceptionAbstract;
use W7\Http\Message\Server\Response;
use Whoops\Exception\Inspector;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

class FatalException extends FatalExceptionAbstract {
	protected function development(): ResponseInterface {
		if ((ENV & BACKTRACE) !== BACKTRACE) {
			$content = 'message: ' . $this->getMessage() . '<br/>file: ' . $this->getPrevious()->getFile() . '<br/>line: ' . $this->getPrevious()->getLine();
		} else {
			ob_start();
			$render = new PlainTextHandler();
			$render->setException($this->getPrevious());
			$render->setInspector(new Inspector($this->getPrevious()));
			$render->setRun(new Run());
			$render->handle();
			$content = ob_get_clean();
		}

		ioutputer()->error($content);
		return new Response();
	}

	protected function release(): ResponseInterface {
		return $this->development();
	}
}
