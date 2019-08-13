<?php


namespace W7\Core\Session\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\App;
use W7\Core\Middleware\MiddlewareAbstract;
use W7\Core\Session\Session;

class SessionMiddleware extends MiddlewareAbstract {
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$request->session = new Session($request);
		$request->session->gc();

		App::getApp()->getContext()->setResponse($request->session->replenishResponse(App::getApp()->getContext()->getResponse()));

		return $handler->handle($request);
	}
}