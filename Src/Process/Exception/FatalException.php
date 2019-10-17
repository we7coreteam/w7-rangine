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

namespace W7\Process\Exception;

use Psr\Http\Message\ResponseInterface;
use W7\Core\Exception\FatalExceptionAbstract;
use W7\Http\Message\Server\Response;

class FatalException extends FatalExceptionAbstract {
	protected function development(): ResponseInterface {
		$content = "exec process fail with \nmessage: " . $this->getMessage() . "\nfile: " . $this->getPrevious()->getFile() . "\nline: " . $this->getPrevious()->getLine();
		ioutputer()->error($content);
		return new Response();
	}

	protected function release(): ResponseInterface {
		return new Response();
	}
}
