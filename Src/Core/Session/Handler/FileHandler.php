<?php

namespace W7\Core\Session\Handler;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FileHandler extends HandlerAbstract {
	/**
	 * @var Filesystem
	 */
	private $filesystem;
	private static $directory;
	private static $hasSet;


	protected function init() {
		$this->filesystem = new Filesystem();
		$this->setPath();
	}

	private function setPath() {
		if (self::$hasSet) {
			return true;
		}

		self::$directory = session_save_path();
		$this->ensureCacheDirectoryExists(self::$directory);

		$openBaseDir = ini_get('open_basedir');
		ini_set('open_basedir', $openBaseDir . ':' . self::$directory);
		if (!file_exists(self::$directory) || !is_writeable(self::$directory)) {
			self::$directory = '/tmp/session';
			$this->ensureCacheDirectoryExists(self::$directory);
		}
		ini_set('open_basedir', $openBaseDir . ':' . self::$directory);
		if (!file_exists(self::$directory) || !is_writeable(self::$directory)) {
			throw new \RuntimeException('session path ' . self::$directory . ' not exist or no permission');
		}

		self::$hasSet = true;
	}

	private function getPayload($key) {
		$path = $this->getPath($key);

		try {
			$expire = substr(
				$contents = $this->filesystem->get($path, true), 0, 10
			);
		} catch (Exception $e) {
			$this->destroy($key);
			return [];
		}

		if (time() >= $expire) {
			$this->destroy($key);
			return [];
		}

		return substr($contents, 10);
	}

	private function getPath($key) {
		$parts = array_slice(str_split($hash = sha1($key), 2), 0, 2);

		return self::$directory.'/'.implode('/', $parts).'/'.$hash;
	}

	private function expiration($seconds) {
		return ($seconds === null || $seconds === 0) ? 9999999999 : ($seconds + time());
	}

	private function ensureCacheDirectoryExists($path) {
		if (!$this->filesystem->exists($path)) {
			$this->filesystem->makeDirectory($path, 0777, true, true);
		}
	}


	public function write($session_id, $session_data) {
		$this->ensureCacheDirectoryExists(dirname($path = $this->getPath($session_id)));
		$result = $this->filesystem->put(
			$path, $this->expiration($this->getExpires()).$session_data, true
		);

		return $result !== false && $result > 0;
	}

	public function read($session_id) {
		return $this->getPayload($session_id);
	}

	public function destroy($session_id) {
		if ($this->filesystem->exists($file = $this->getPath($session_id))) {
			return $this->filesystem->delete($file);
		}

		return false;
	}

	public function gc($maxlifetime) {
		$files = Finder::create()
			->in(self::$directory)
			->files()
			->ignoreDotFiles(true)
			->date('<= now - '.$maxlifetime.' seconds');

		foreach ($files as $file) {
			$this->filesystem->delete($file->getRealPath());
		}
	}
}