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
use W7\Console\Io\Output;
use W7\Core\Process\ProcessAbstract;

class ReloadProcess extends ProcessAbstract {
	private static array $watchDir = [];
	private static array $fileTypes = [
		'php'
	];
	private string $md5File = '';

	public function __construct($name, $num = 1, Process $process = null) {
		self::$watchDir = [
			App::getApp()->getAppPath(),
			App::getApp()->getBasePath() . '/'. 'config'
		];
		parent::__construct($name, $num, $process);

		$reloadConfig = $this->getConfig()->get('reload');
		self::$watchDir = array_merge(self::$watchDir, $reloadConfig['path'] ?? []);
		self::$fileTypes = array_merge(self::$fileTypes, $reloadConfig['type'] ?? []);
	}

	public static function addDir(string $dir): void {
		self::$watchDir[] = $dir;
	}

	public static function addType($type): void {
		self::$fileTypes[] = trim($type, '.');
	}

	protected function beforeStart(): void {
		(new Output())->info('>> server hot reload start');
	}

	public function check(): bool {
		return true;
	}

	protected function run(Process $process): void {
		$this->md5File = $this->getWatchDirMd5();

		$server = App::$server;
		itimeTick(1000, function () use ($server) {
			$md5File = $this->getWatchDirMd5();
			$startReload = (strcmp($this->md5File, $md5File) !== 0);
			$this->md5File = $md5File;

			if ($startReload) {
				$server->isRun();
				$server->getServer()->reload();

				(new Output())->writeln('Reloaded in ' . date('m-d H:i:s') . '...');
			}
		});
	}

	private function md5File(string $dir): string {
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
				if (in_array($extension, self::$fileTypes, true)) {
					$md5File[] = md5_file($dir . '/' . $entry);
				}
				$md5File[] = $entry;
			}
		}
		$d->close();

		return md5(implode('', $md5File));
	}

	private function getWatchDirMd5(): string {
		$md5 = [];
		foreach (self::$watchDir as $dir) {
			$md5[] = $this->md5File($dir);
		}
		return md5(implode('', $md5));
	}
}
