<?php

namespace W7\Core\Session\Handler;

class CacheHandler extends HandlerAbstract {
	private function getCache() {
		return icache()->channel($this->config['cache_channel'] ?? 'default');
	}

	public function set($key, $value, $ttl) {
		$session = $this->getCache()->get($this->getId());
		$session[$key] = $value;
		return $this->getCache()->set($this->getId(), $session, $ttl);
	}

	public function get($key, $default = '') {
		$session = $this->getCache()->get($this->getId());
		return $session[$key] ?? $default;
	}

	public function has($key) {
		$session = $this->getCache()->get($this->getId());
		return isset($session[$key]);
	}

	public function destroy() {
		return $this->getCache()->delete($this->getId());
	}
}