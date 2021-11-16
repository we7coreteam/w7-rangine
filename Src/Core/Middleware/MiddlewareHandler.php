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
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\App;

class MiddlewareHandler implements RequestHandlerInterface {
	/**
	* @var array
	*/
	private array $middlewares;

	/**
	 * @var integer
	 *
	 */
	private int $offset = 0;

	/**
	 * MiddlewareHandler constructor.
	 *
	 * @param array $middleware
	 */
	public function __construct(array $middleware) {
		$this->middlewares = $middleware;
	}

	/**
	 * Process the request using the current middleware.
	 *
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface
	 * @throws \InvalidArgumentException
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface {
		$handlerMiddleware = $this->middlewares[$this->offset];

		$handler = $handlerMiddleware['class'];
		if (!class_exists($handler)) {
			throw new \InvalidArgumentException($handler . ' Handler not found.');
		}

		$handler = App::getApp()->getContainer()->get($handler);
		if (!$handler instanceof MiddlewareInterface) {
			throw new \InvalidArgumentException('Invalid Handler. It must be an instance of MiddlewareInterface');
		}

		return $handler->process($request, $this->next(), ...($handlerMiddleware['arg'] ?? []));
	}

	/**
	 * Get a handler pointing to the next middleware.
	 *
	 * @return static
	 */
	private function next(): MiddlewareHandler {
		$clone = clone $this;
		$clone->offset++;
		return $clone;
	}
}
