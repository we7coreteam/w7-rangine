<?php
/**
 * @author donknap
 * @date 18-7-25 下午3:03
 */

namespace W7\Core\UserProcess\Process;

use W7\App;
use W7\Core\Process\ProcessAbstract;

class ReloadProcess extends ProcessAbstract {

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
	protected $interval = 5;

	private $debug = false;

	protected function init() {
		$reloadConfig = \iconfig()->getUserAppConfig('reload');
		$this->interval = !empty($reloadConfig['interval']) ? $reloadConfig['interval'] : $this->interval;
		$this->debug = (bool)$reloadConfig['debug'];
		$this->watchDir = array_merge($this->watchDir, $reloadConfig['path'] ?? []);

		$this->md5File = $this->getWatchDirMd5();

	}

	protected function beforeStart() {
		if ($this->debug) {
			ioutputer()->writeln("Start automatic reloading every {$this->interval} seconds ...");
		}
	}

	public function run() {
		if ($this->debug) {
			$startReload = true;
		} else {
			$md5File = $this->getWatchDirMd5();
			$startReload = (strcmp($this->md5File, $md5File) !== 0);
			$this->md5File = $md5File;
		}
		if ($startReload) {
			App::$server->isRun();
			App::$server->getServer()->reload();
			if (ini_get('opcache.enable') || ini_get('opcache.enable_cli')) {
				opcache_reset();
			}
			if (!$this->debug) {
				ioutputer()->writeln("Reloaded in " . date('m-d H:i:s') . "...");
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
		$d	   = dir($dir);
		while (false !== ($entry = $d->read())) {
			if ($entry !== '.' && $entry !== '..') {
				if (is_dir($dir . '/' . $entry)) {
					$md5File[] = $this->md5File($dir . '/' . $entry);
				} elseif (substr($entry, -4) === '.php') {
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
