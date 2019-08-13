<?php

namespace W7\Core\Session\Handler;

class CacheHandler extends HandlerAbstract {
	private function getCache() {
		return icache()->channel($this->config['cache_channel'] ?? 'default');
	}

	public function destroy($session_id) {
		return $this->getCache()->delete($session_id);
	}

	public function write($session_id, $session_data) {
		return $this->getCache()->set($session_id, $session_data, $this->getExpires());
	}

	public function read($session_id) {
		return $this->getCache()->get($session_id);
	}
}