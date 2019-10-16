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

namespace W7\Reload\Process;

use Swoole\Process;
use W7\App;
use W7\Core\Process\ProcessAbstract;

class ReloadProcess extends ProcessAbstract {
	/**
	 * 监听文件变化的路径
	 *
	 * @var string
	 */
	private static $watchDir = [
		APP_PATH,
		BASE_PATH. DIRECTORY_SEPARATOR. 'config'
	];

	private static $fileTypes = [
		'php'
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
	protected $interval = 1;

	/**
	 * 初始化方法
	 */
	public function __construct($name, $num = 1, Process $process = null) {
		parent::__construct($name, $num, $process);

		$reloadConfig = \iconfig()->getUserAppConfig('reload');
		self::$watchDir = array_merge(self::$watchDir, $reloadConfig['path'] ?? []);
		self::$fileTypes = array_merge(self::$fileTypes, $reloadConfig['type'] ?? []);

		$this->md5File = $this->getWatchDirMd5();
	}

	public static function addDir(string $dir) {
		self::$watchDir[] = $dir;
	}

	public static function addType($type) {
		self::$fileTypes[] = trim($type, '.');
	}

	protected function beforeStart() {
		ioutputer()->info('>> server hot reload start');
	}

	public function check() {
		return true;
	}

	protected function run() {
		$server = App::$server;
		$md5File = $this->getWatchDirMd5();
		$startReload = (strcmp($this->md5File, $md5File) !== 0);
		$this->md5File = $md5File;

		if ($startReload) {
			$server->isRun();
			$server->getServer()->reload();

			ioutputer()->writeln('Reloaded in ' . date('m-d H:i:s') . '...');
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
				}
				$extension = pathinfo($entry, PATHINFO_EXTENSION);
				if (in_array($extension, self::$fileTypes)) {
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
		foreach (self::$watchDir as $dir) {
			$md5[] = $this->md5File($dir);
		}
		return md5(implode('', $md5));
	}
}
