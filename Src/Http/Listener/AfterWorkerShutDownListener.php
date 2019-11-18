<?php

namespace W7\Http\Listener;

use W7\Core\Helper\Storage\Context;
use W7\Core\Listener\ListenerAbstract;
use W7\Http\Message\Server\Response;

class AfterWorkerShutDownListener extends ListenerAbstract {
	public function run(...$params) {
		/**
		 * @var Response $response
		 */
		$response = $params[1];
		$contexts = icontext()->all();
		foreach ($contexts as $id => $context) {
			if (!empty($context[Context::RESPONSE_KEY])) {
				/**
				 * @var Response $cResponse
				 */
				$cResponse = $context[Context::RESPONSE_KEY];
				$cResponse = $cResponse->withHeaders($response->getHeaders())->withContent($response->getBody()->getContents());
				$cResponse->send();
			}
		}
	}
}