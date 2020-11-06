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

namespace W7\Http\Session\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\Core\Facades\Container;
use W7\Core\Facades\Context;
use W7\Core\Middleware\MiddlewareAbstract;
use W7\Core\Session\Session;

class SessionMiddleware extends MiddlewareAbstract {
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$request->session = Container::clone(Session::class);
		$request->session->start($request);
		$request->session->gc();

		if ($request->session->all()) {
			Context::setResponse($request->session->replenishResponse(Context::getResponse()));
		}

		return $handler->handle($request);
	}
}
