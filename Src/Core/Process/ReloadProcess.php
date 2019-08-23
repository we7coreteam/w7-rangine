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

namespace W7\Core\Process;

use Swoole\Process;
use W7\App;

class ReloadProcess implements ProcessInterface {
	/**
	 * 监听文件变化的路径
	 *
	 * @var string
	 */
	private $watchDir = [
		APP_PATH,
		BASE_PATH. DIRECTORY_SEPARATOR. 'config',
		BASE_PATH. DIRECTORY_SEPARATOR. 'view'
	];

	/**
	 * the lasted md5 of dir
	 *
	 * @var string
	 */
	private $md5File = '';

	/**
	 * the interval of scan
	 *
	 * @var int
	 */
	private $interval = 1;

	private $enabled = false;

	/**
	 * 初始化方法
	 */
	public function __construct() {
		$this->enabled = ((ENV & DEBUG) === DEBUG);
		$reloadConfig = \iconfig()->getUserAppConfig('reload');
		$this->watchDir = array_merge($this->watchDir, $reloadConfig['path'] ?? []);

		$this->md5File = $this->getWatchDirMd5();
	}

	public function check() {
		if ($this->enabled) {
			return true;
		}
		return false;
	}

	public function run(Process $process) {
		ioutputer()->writeln("Start automatic reloading every {$this->interval} seconds ...");

		$server = App::$server;
		while (true) {
			sleep($this->interval);

			$md5File = $this->getWatchDirMd5();
			$startReload = (strcmp($this->md5File, $md5File) !== 0);
			$this->md5File = $md5File;

			if ($startReload) {
				$server->isRun();
				$server->getServer()->reload();

				ioutputer()->writeln('Reloaded in ' . date('m-d H:i:s') . '...');
			}
		}
	}

	/**
	 * md5 of dir
	 *
	 * @param string $dir
	 *
	 * @return bool|string
	 */
	private function md5File($dir) {
		if (!is_dir($dir)) {
			return '';
		}

		$md5File = array();
		$d = dir($dir);
		while (false !== ($entry = $d->read())) {
			if ($entry !== '.' && $entry !== '..') {
				if (is_dir($dir . '/' . $entry)) {
					$md5File[] = $this->md5File($dir . '/' . $entry);
				} elseif (substr($entry, -4) === '.php' || substr($entry, -5) === '.html') {
					$md5File[] = md5_file($dir . '/' . $entry);
				}
				$md5File[] = $entry;
			}
		}
		$d->close();

		return md5(implode('', $md5File));
	}

	private function getWatchDirMd5() {
		$md5 = [];
		foreach ($this->watchDir as $dir) {
			$md5[] = $this->md5File($dir);
		}
		return md5(implode('', $md5));
	}
}
