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

namespace W7\Tcp\Listener;

use W7\App;
use W7\Core\Exception\HandlerExceptions;
use W7\Core\Helper\Storage\Context;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Http\Message\Server\Response;

class AfterWorkerShutDownListener extends ListenerAbstract {
	public function run(...$params) {
		$contexts = icontext()->all();
		/**
		 * @var Response $response
		 */
		$response = iloader()->get(HandlerExceptions::class)->handle($params[1], ServerEnum::TYPE_TCP);
		foreach ($contexts as $id => $context) {
			if (!empty($context[Context::RESPONSE_KEY]) && $context['data']['server-type'] == ServerEnum::TYPE_TCP) {
				App::$server->getServer()->send($context['data']['fd'], $response->getBody()->getContents());
				App::$server->getServer()->close($context['data']['fd']);
			}
		}
	}
}
