<?php

namespace W7\Core\Session\Handler;

interface HandlerInterface {
	public function set($key, $value, $ttl);

	public function get($key);

	public function clear();

	public function has($key);
}