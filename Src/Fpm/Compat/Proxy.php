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

namespace W7\Fpm\Compat;

use ArrayAccess;
use Hyperf\SuperGlobals\Exception\RequestNotFoundException;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;

abstract class Proxy implements Arrayable, ArrayAccess, JsonSerializable {
	public function jsonSerialize() {
		return $this->toArray();
	}

	public function offsetExists($offset) {
		$data = $this->toArray();
		return isset($data[$offset]);
	}

	public function offsetGet($offset) {
		return $this->toArray()[$offset] ?? null;
	}

	public function offsetSet($offset, $value) {
		$data = $this->toArray();
		$data[$offset] = $value;
		$request = $this->override($this->getRequest(), $data);
		icontext()->setRequest($request);
	}

	public function offsetUnset($offset) {
		$data = $this->toArray();
		unset($data[$offset]);
		$request = $this->override($this->getRequest(), $data);
		icontext()->setRequest($request);
	}

	protected function getRequest(): ServerRequestInterface {
		$request = icontext()->getRequest();
		if (! $request instanceof ServerRequestInterface) {
			throw new RequestNotFoundException(sprintf('%s is not found.', ServerRequestInterface::class));
		}

		return $request;
	}

	abstract protected function override(ServerRequestInterface $request, array $data): ServerRequestInterface;
}
