<?php

namespace W7\Core\Session\Handler;

use Exception;
use Illuminate\Filesystem\Filesystem;

class FileHandler extends HandlerAbstract {
	/**
	 * @var Filesystem
	 */
	private $filesystem;
	private $directory;


	protected function init() {
		if (empty($this->config['path'])) {
			$this->config['path'] = RUNTIME_PATH . '/cache/session';
		}
		$this->directory = $this->config['path'];
		$this->filesystem = new Filesystem();
	}

	public function set($key, $value, $ttl) {
		$this->ensureCacheDirectoryExists($path = $this->getPath($this->getId()));
		$session = $this->getPayload($this->getId());
		$session[$key] = $value;

		$result = $this->filesystem->put(
			$path, $this->expiration($ttl).serialize($session), true
		);

		return $result !== false && $result > 0;
	}

	public function get($key, $default = '') {
		$session = $this->getPayload($this->getId());
		return $session[$key] ?? $default;
	}

	public function has($key) {
		$session = $this->getPayload($this->getId());
		return isset($session[$key]) ? true : false;
	}

	public function destroy() {
		if ($this->filesystem->exists($file = $this->getPath($this->getId()))) {
			return $this->filesystem->delete($file);
		}

		return false;
	}

	private function getPayload($key) {
		$path = $this->getPath($key);

		try {
			$expire = substr(
				$contents = $this->filesystem->get($path, true), 0, 10
			);
		} catch (Exception $e) {
			$this->destroy();
			return [];
		}

		if (time() >= $expire) {
			$this->destroy();
			return [];
		}

		return unserialize(substr($contents, 10));
	}

	private function getPath($key) {
		$parts = array_slice(str_split($hash = sha1($key), 2), 0, 2);

		return $this->directory.'/'.implode('/', $parts).'/'.$hash;
	}

	private function ensureCacheDirectoryExists($path) {
		if (!$this->filesystem->exists(dirname($path))) {
			$this->filesystem->makeDirectory(dirname($path), 0777, true, true);
		}
	}

	private function expiration($seconds) {
		return ($seconds === null || $seconds === 0) ? 9999999999 : ($seconds + time());
	}
}