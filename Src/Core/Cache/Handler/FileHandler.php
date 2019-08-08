<?php

namespace W7\Core\Cache\Handler;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Psr\SimpleCache\CacheInterface;

class FileHandler implements CacheInterface {
	/**
	 * @var Filesystem
	 */
	private $filesystem;
	private $directory;


	public function __construct($directory) {
		$this->directory = $directory;
		$this->filesystem = neW Filesystem();
	}

	public function set($key, $value, $ttl = null) {
		$this->ensureCacheDirectoryExists($path = $this->getPath($key));

		$result = $this->filesystem->put(
			$path, $this->expiration($ttl).$value, true
		);

		return $result !== false && $result > 0;
	}

	public function get($key, $default = null) {
		return $this->getPayload($key) ?? $default;
	}

	public function has($key) {
		return $this->getPayload($key) !== null ? true : false;
	}

	public function setMultiple($values, $ttl = null) {
		foreach ($values as $key => $value) {
			$this->set($key, $value, $ttl);
		}
	}

	public function getMultiple($keys, $default = null) {
		$values = [];
		foreach ($keys as $key => $name) {
			$values[$key] = $this->get($name, $default);
		}
		return $values;
	}

	public function delete($key) {
		if ($this->filesystem->exists($file = $this->getPath($key))) {
			return $this->filesystem->delete($file);
		}

		return false;
	}

	public function deleteMultiple($keys) {
		foreach ($keys as $key) {
			$this->delete($key);
		}
	}

	public function clear() {
		if (! $this->filesystem->isDirectory($this->directory)) {
			return false;
		}

		foreach ($this->filesystem->directories($this->directory) as $directory) {
			if (! $this->filesystem->deleteDirectory($directory)) {
				return false;
			}
		}

		return true;
	}

	private function getPath($key) {
		$parts = array_slice(str_split($hash = sha1($key), 2), 0, 2);

		return $this->directory.'/'.implode('/', $parts).'/'.$hash;
	}

	protected function getPayload($key) {
		$path = $this->getPath($key);

		try {
			$expire = substr(
				$contents = $this->filesystem->get($path, true), 0, 10
			);
		} catch (Exception $e) {
			$this->delete($key);
			return null;
		}

		if (time() >= $expire) {
			$this->delete($key);
			return null;
		}

		return substr($contents, 10);
	}

	private function ensureCacheDirectoryExists($path) {
		if (! $this->filesystem->exists(dirname($path))) {
			$this->filesystem->makeDirectory(dirname($path), 0777, true, true);
		}
	}

	private function expiration($seconds) {
		return ($seconds === null || $seconds === 0) ? 9999999999 : ($seconds + time());
	}
}