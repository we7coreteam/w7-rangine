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

namespace W7\Core\Config\Env;

use Dotenv\Dotenv;
use Dotenv\Environment\DotenvFactory;

class Env {
	private $envPath = '';

	private $hostName = '';

	private $defaultName = '.env';

	public function __construct($path) {
		if (empty($path) || !is_dir($path)) {
			throw new \RuntimeException('Invalid env path');
		}
		$this->hostName = gethostname();
		$this->envPath = $path;
	}

	public function load() {
		//加载当前环境的.env，覆盖默认的.env数据
		$envName = getenv('ENV_NAME') ?: 'default';

		$envFileName = $this->getEnvFileByHostName($envName);
		if (!empty($envFileName) && file_exists($this->envPath . '/' . $envFileName)) {
			putenv('ENV_NAME=' . $envFileName);
			$_ENV['ENV_NAME'] = $envFileName;
			
			$loader = new Loader(
				$this->getFilePaths((array) $this->envPath, $envFileName ?: '.env'),
				new DotenvFactory(),
				true
			);
			$dotEnv = new Dotenv($loader);
			$dotEnv->overload();
		}
	}

	private function getEnvFileByHostName($hostname = '') {
		if (empty($hostname)) {
			$hostname = $this->hostName;
		}
		if ($hostname == 'default') {
			return $this->defaultName;
		}

		$fileTree = glob(sprintf('%s/.env*', $this->envPath));
		if (empty($fileTree)) {
			return '';
		}

		$envFile = '';
		foreach ($fileTree as $key => $file) {
			$fileName = pathinfo($file, PATHINFO_BASENAME);
			$temp = explode($this->defaultName . '.', $fileName);
			if (!empty($temp[1]) && strpos($hostname, $temp[1]) !== false) {
				$envFile = $fileName;
			}
		}

		return $envFile;
	}

	/**
	 * 覆盖dotenv的方法，dotenv支持自定义load,但是不支持文件格式处理
	 * Returns the full paths to the files.
	 *
	 * @param string[] $paths
	 * @param string   $file
	 *
	 * @return string[]
	 */
	protected function getFilePaths(array $paths, $file) {
		return array_map(function ($path) use ($file) {
			return $path . '/' . $file;
		}, $paths);
	}
}
