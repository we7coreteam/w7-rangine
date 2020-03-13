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
use W7\Core\Helper\StringHelper;
use W7\Fpm\Compat\Proxy;

class Server extends Proxy {
	/**
	 * @var array
	 */
	protected $default;

	public function __construct(array $default = []) {
		$this->default = $default;
	}

	public function toArray(): array {
		$headers = [];
		foreach ($this->getRequest()->getHeaders() as $key => $value) {
			$headers['HTTP_' . str_replace('-', '_', StringHelper::upper($key))] = $value;
		}
		$result = [];
		foreach (array_merge($this->default, $this->getRequest()->getServerParams(), $headers) as $key => $value) {
			$key = StringHelper::upper($key);
			if (is_array($value) && count($value) == 1) {
				$result[$key] = $value[0];
			} else {
				$result[$key] = $value;
			}
		}
		return $result;
	}

	protected function override(ServerRequestInterface $request, array $data): ServerRequestInterface {
		return $request;
	}
}
