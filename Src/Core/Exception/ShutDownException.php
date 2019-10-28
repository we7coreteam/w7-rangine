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

namespace W7\Core\Exception;

use Psr\Http\Message\ResponseInterface;
use W7\App;
use W7\Core\Listener\WorkerStopListener;
use W7\Http\Message\Server\Response;

class ShutDownException extends ResponseExceptionAbstract {
	public function render(): ResponseInterface {
		(new WorkerStopListener())->run(App::$server->getServer(), App::$server->getServer()->worker_id);
		return new Response();
	}
}
