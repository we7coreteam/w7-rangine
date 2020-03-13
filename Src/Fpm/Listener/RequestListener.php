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

namespace W7\Fpm\Listener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use W7\App;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\SwooleEvent;
use W7\Http\Message\Formatter\ResponseFormatterInterface;
use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response as Psr7Response;
use W7\Fpm\Server\Dispatcher;

class RequestListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $request, $response) = $params;
		return $this->dispatch($server, $request, $response);
	}

	/**
	 * @param $server
	 * @param Request $request
	 * @param Response $response
	 */
	private function dispatch($server, Request $psr7Request, Response $response) {
		$context = App::getApp()->getContext();
		$context->setContextDataByKey('workid', $server->worker_id);
		$context->setContextDataByKey('coid', 0);

		$psr7Response = new Psr7Response();
		$psr7Response->setFormatter(iloader()->get(ResponseFormatterInterface::class));

		ievent(SwooleEvent::ON_USER_BEFORE_REQUEST, [$psr7Request, $psr7Response]);

		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = \iloader()->get(Dispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);

		$response->setStatusCode($psr7Response->getStatusCode());
		$response->setContent($psr7Response->getBody()->getContents());
		$response->headers->add($psr7Response->getHeaders());
		/**
		 * @var \W7\Http\Message\Base\Cookie $cookie
		 */
		foreach ($psr7Response->getCookies() as $cookie) {
			$response->headers->setCookie(Cookie::create($cookie->getName(), $cookie->getValue(), $cookie->getExpires(), $cookie->getPath(), $cookie->getDomain()));
		}
		$response->send();

		ievent(SwooleEvent::ON_USER_AFTER_REQUEST);
	}
}
