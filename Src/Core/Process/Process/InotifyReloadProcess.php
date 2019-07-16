<?php

namespace W7\Core\Process\Process;

use W7\App;
use W7\Core\Process\ProcessAbstract;

class InotifyReloadProcess extends ProcessAbstract {
	/**
	 * 监听文件变化的路径
	 *
	 * @var string
	 */
	private $watchDir = [
		APP_PATH,
		BASE_PATH. DIRECTORY_SEPARATOR. 'config'
	];

	private $debug = false;

	protected function init() {
		$reloadConfig = \iconfig()->getUserAppConfig('reload');
		$this->interval = !empty($reloadConfig['interval']) ? $reloadConfig['interval'] : $this->interval;
		$this->debug = (bool)$reloadConfig['debug'];
		$this->watchDir = array_merge($this->watchDir, $reloadConfig['path'] ?? []);
	}

	protected function beforeStart() {
		if ($this->debug) {
			ioutputer()->writeln("Start automatic reloading every {$this->interval} seconds ...");
		}

		$this->pipe = inotify_init();
		foreach ($this->watchDir as $dir) {
			foreach ($this->getAllDirs($dir) as $childDir) {
				inotify_add_watch($this->pipe, $childDir, IN_MODIFY);
			}
		}
	}

	public function read() {
		$events = inotify_read($this->pipe);
		if ($events) {
			App::$server->getServer()->reload();
			if (ini_get('opcache.enable') || ini_get('opcache.enable_cli')) {
				opcache_reset();
			}
			if (!$this->debug) {
				ioutputer()->writeln("Reloaded in " . date('m-d H:i:s') . "...");
			}
		}
	}

	// 使用迭代器遍历目录
	private function getAllDirs($dir) {
		$d	= dir($dir);
		$files = [];
		while (false !== ($entry = $d->read())) {
			if ($entry !== '.' && $entry !== '..') {
				if (is_dir($dir . '/' . $entry)) {
					$files[] = $dir . '/' . $entry;
					$files = array_merge($this->getAllDirs($dir . '/' . $entry), $files);
				}
			}
		}

		return $files;
	}
}