<?php
/**
 * @author donknap
 * @date 18-7-25 下午3:03
 */

namespace W7\Core\Process;

use Swoole\Process;
use W7\App;
use W7\Core\Helper\FileHelper;

class ReloadProcess implements ProcessInterface {

	/**
	 * 监听文件变化的路径
	 *
	 * @var string
	 */
	private $watchDir = [
		APP_PATH,
		BASE_PATH. DIRECTORY_SEPARATOR. 'config'
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
	private $interval = 5;

	private $enabled = false;

	private $debug = false;

	/**
	 * 初始化方法
	 */
	public function __construct() {
		$this->md5File = $this->getWatchDirMd5();

		$reloadConfig = \iconfig()->getUserAppConfig('reload');
		$this->interval = !empty($reloadConfig['interval']) ? $reloadConfig['interval'] : $this->interval;
		$this->enabled = (bool)$reloadConfig['enabled'];
		$this->debug = (bool)$reloadConfig['debug'];
	}
	public function check() {
		if ($this->enabled) {
			return true;
		}
		return false;
	}

	public function run(Process $process) {
		$server = App::$server;
		if ($this->debug) {
			ioutputer()->writeln("Start automatic reloading every {$this->interval} seconds ...");
		}
		while (true) {
			sleep($this->interval);
			if ($this->debug) {
				$startReload = true;
			} else {
				$md5File = $this->getWatchDirMd5();
				$startReload = (strcmp($this->md5File, $md5File) !== 0);
				$this->md5File = $md5File;
			}
			if ($startReload) {
				$server->isRun();
				$server->getServer()->reload();
				if (ini_get('opcache.enable') || ini_get('opcache.enable_cli')) {
					opcache_reset();
				}
				if (!$this->debug) {
					ioutputer()->writeln("Reloaded in " . date('m-d H:i:s') . "...");
				}
			}
		}
	}

	private function getWatchDirMd5() {
		$md5 = [];
		foreach ($this->watchDir as $dir) {
			$md5[] = FileHelper::md5File($dir);
		}
		return md5(implode('', $md5));
	}
}
