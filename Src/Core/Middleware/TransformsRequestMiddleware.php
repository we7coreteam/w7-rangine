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

use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class TransformsRequestMiddleware extends MiddlewareAbstract {
	/**
	 * @throws \ReflectionException
	 * @throws BindingResolutionException
	 */
	final public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$request = $this->trans($request);
		return $handler->handle($request);
	}

	/**
	 * Clean the request's data.
	 *
	 * @param ServerRequestInterface $request
	 * @return ServerRequestInterface
	 * @throws BindingResolutionException
	 * @throws \ReflectionException
	 */
	protected function trans(ServerRequestInterface $request) : ServerRequestInterface {
		$request = $request->withQueryParams($this->transArray($request->getQueryParams()));
		$request = $request->withParsedBody($this->transArray($request->getParsedBody()));

		$this->getContext()->setRequest($request);

		return $request;
	}

	/**
	 * Clean the data in the given array.
	 *
	 * @param  array  $data
	 * @param string $keyPrefix
	 * @return array
	 */
	protected function transArray(array $data, string $keyPrefix = ''): array {
		return collect($data)->map(function ($value, $key) use ($keyPrefix) {
			return $this->transValue($keyPrefix.$key, $value);
		})->all();
	}

	/**
	 * Clean the given value.
	 *
	 * @param string $key
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function transValue(string $key, mixed $value): mixed {
		if (is_array($value)) {
			return $this->transArray($value, $key.'.');
		}

		return $this->transform($key, $value);
	}

	/**
	 * Transform the given value.
	 *
	 * @param string $key
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function transform(string $key, mixed $value): mixed {
		return $value;
	}
}
