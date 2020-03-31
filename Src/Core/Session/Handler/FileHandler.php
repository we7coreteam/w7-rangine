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

namespace W7\Core\Session\Handler;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FileHandler extends HandlerAbstract {
	/**
	 * @var Filesystem
	 */
	private $filesystem;
	private $directory;

	protected function init() {
		$this->filesystem = new Filesystem();
		$this->setPath();
	}

	public function getUserSessionSavePath() {
		return empty($this->config['save_path']) ? '/tmp/session' : $this->config['save_path'];
	}

	private function setPath() {
		$this->directory = $this->getUserSessionSavePath();
		$this->ensureSessionDirectoryExists($this->directory);
		if (!$this->filesystem->isWritable($this->directory) || !$this->filesystem->isReadable($this->directory)) {
			throw new \RuntimeException('session path ' . $this->directory . ' not exist or no permission');
		}
	}

	private function getPayload($key) {
		$path = $this->getPath($key);

		try {
			$expire = substr(
				$contents = $this->filesystem->get($path, true),
				0,
				10
			);
		} catch (\Throwable $e) {
			$this->destroy($key);
			return '';
		}

		if (time() >= $expire) {
			$this->destroy($key);
			return '';
		}

		return substr($contents, 10);
	}

	private function getPath($key) {
		$parts = array_slice(str_split($hash = sha1($key), 2), 0, 2);

		return $this->directory . '/'.implode('/', $parts) . '/' . $hash;
	}

	private function expiration($seconds) {
		return ($seconds === null || $seconds === 0) ? 9999999999 : ($seconds + time());
	}

	private function ensureSessionDirectoryExists($path) {
		if (!$this->filesystem->exists($path)) {
			$this->filesystem->makeDirectory($path, 0777, true, true);
		}
	}

	public function write($session_id, $session_data) {
		$this->ensureSessionDirectoryExists(dirname($path = $this->getPath($session_id)));
		$result = $this->filesystem->put(
			$path,
			$this->expiration($this->getExpires()).$session_data,
			true
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
			->in($this->directory)
			->files()
			->ignoreDotFiles(true)
			->date('<= now - '.$maxlifetime.' seconds');

		foreach ($files as $file) {
			$this->filesystem->delete($file->getRealPath());
		}
	}
}
