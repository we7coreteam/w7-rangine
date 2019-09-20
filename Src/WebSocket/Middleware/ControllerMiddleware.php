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

namespace W7\WebSocket\Middleware;

use W7\App;
use W7\Core\Middleware\ControllerMiddleware as ControllerMiddlewareAbstract;
use W7\WebSocket\Message\Message;

class ControllerMiddleware extends ControllerMiddlewareAbstract {
	protected function parseResponse($response) {
		if ($response instanceof Message) {
			$response = App::getApp()->getContext()->getResponse()->withData($response);
		}

		parent::parseResponse($response);
	}
}
