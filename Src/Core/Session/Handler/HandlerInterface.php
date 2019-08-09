<?php

namespace W7\Core\Session\Handler;

interface HandlerInterface {
	public function setId($id);

	public function getId($hasPrefix = true);

	public function set($key, $value, $ttl);

	public function get($key, $default = '');

	public function has($key);

	public function destroy();
}