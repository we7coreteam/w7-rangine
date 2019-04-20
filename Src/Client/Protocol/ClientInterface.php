<?php

namespace W7\Client\Protocol;

interface ClientInterface {
	public function __construct(array $params);
	public function call($url, $params = null);
	public function pack($data);
	public function unpack($data);
}