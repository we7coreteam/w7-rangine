<?php
/**
 * @author donknap
 * @date 18-12-30 下午5:21
 */

namespace W7\Core\Cache\Connection;

use W7\Core\Cache\ConnectorManager;

abstract class ConnectionAbstract implements ConnectionInterface {
	protected $handle;
	protected $release = true;

	public function noRelease() {
		$this->release = false;
		return $this;
	}

	public function connect($config) {
		return $this->handle = $this->open($config);
	}

	public function getHandle() {
		return $this->handle;
	}

	public function setHandle($handle) {
		$this->handle = $handle;
	}

	public function __destruct() {
		if ($this->release) {
			iloader()->singleton(ConnectorManager::class)->release($this->handle);
		} else {
			$this->close();
		}
	}
}