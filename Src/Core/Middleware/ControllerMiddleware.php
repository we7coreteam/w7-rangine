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

namespace W7\Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;

class ControllerMiddleware extends MiddlewareAbstract {
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		/**
		 * @var Request $request
		 */
		$this->getContext()->setResponse($this->parseResponse($request->route->run($request)));

		return $handler->handle($request);
	}

	protected function parseResponse($response) {
		if ($response instanceof Response) {
			return $response;
		}

		if (is_object($response)) {
			$response = 'Illegal type ' . get_class($response) . ', Must be a response object, an array, or a string';
		}

		return $this->getContext()->getResponse()->json($response);
	}
}
