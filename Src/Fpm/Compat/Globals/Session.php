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

namespace W7\Fpm\Compat\Globals;

use Psr\Http\Message\ServerRequestInterface;
use W7\Fpm\Compat\Proxy;

class Session extends Proxy {
	public function toArray(): array {
		return $this->getSession()->all();
	}

	public function offsetExists($offset) {
		return $this->getSession()->has($offset);
	}

	public function offsetGet($offset) {
		return $this->getSession()->get($offset);
	}

	public function offsetSet($offset, $value) {
		$this->getSession()->set($offset, $value);
	}

	public function offsetUnset($offset) {
		$this->getSession()->delete($offset);
	}

	protected function getSession(): \W7\Core\Session\Session {
		$request = $this->getRequest();
		return $request->session;
	}

	protected function override(ServerRequestInterface $request, array $data): ServerRequestInterface {
		foreach ($data as $key => $datum) {
			$request->session->set($key, $datum);
		}

		return $request;
	}
}
