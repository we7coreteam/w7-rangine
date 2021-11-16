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

use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\Contract\Session\SessionInterface;
use W7\Core\Middleware\MiddlewareAbstract;

class SessionMiddleware extends MiddlewareAbstract {
	/**
	 * @throws \ReflectionException
	 * @throws BindingResolutionException
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$request->session = $this->getContainer()->clone(SessionInterface::class);
		$request->session->start($request);
		$request->session->gc();

		$this->getContext()->setResponse($request->session->replenishResponse($this->getContext()->getResponse()));

		return $handler->handle($request);
	}
}
