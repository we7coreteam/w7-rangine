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

use W7\Http\Message\Server\Response;

class DefaultExceptionHandler extends HandlerAbstract {
	public function handle(\Throwable $e): Response {
		$message = 'message: ' . $e->getMessage() . "\n" . 'file: ' . $e->getFile() . "\n" . 'line: ' . $e->getLine();
		if (isCli()) {
			ioutputer()->error($message);
		} else {
			trigger_error($message);
		}

		return $this->getResponse();
	}
}
