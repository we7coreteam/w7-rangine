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
use W7\Core\Facades\Context;

abstract class TransformsRequestMiddleware extends MiddlewareAbstract {
	final public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$request = $this->trans($request);
		return $handler->handle($request);
	}

	/**
	 * Clean the request's data.
	 *
	 * @param  ServerRequestInterface  $request
	 * @return void
	 */
	protected function trans($request) : ServerRequestInterface {
		$request = $request->withQueryParams($this->transArray($request->getQueryParams()));
		$request = $request->withParsedBody($this->transArray($request->getParsedBody()));

		Context::setRequest($request);

		return $request;
	}

	/**
	 * Clean the data in the given array.
	 *
	 * @param  array  $data
	 * @param  string  $keyPrefix
	 * @return array
	 */
	protected function transArray(array $data, $keyPrefix = '') {
		return collect($data)->map(function ($value, $key) use ($keyPrefix) {
			return $this->transValue($keyPrefix.$key, $value);
		})->all();
	}

	/**
	 * Clean the given value.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function transValue($key, $value) {
		if (is_array($value)) {
			return $this->transArray($value, $key.'.');
		}

		return $this->transform($key, $value);
	}

	/**
	 * Transform the given value.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function transform($key, $value) {
		return $value;
	}
}
