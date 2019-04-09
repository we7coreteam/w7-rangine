<?php
/**
 * 根据当前Hostname获取env值
 * @author donknap
 * @date 19-4-9 上午10:21
 */

namespace W7\Core\Config;


use Dotenv\Dotenv;

class Env {
	private $envPath = '';

	private $hostName = '';

	private $defaultName = '.env';

	public function __construct($path) {
		if (empty($path) || !is_dir($path)) {
			throw new \RuntimeException('Invalid env path');
		}
		$this->envPath = rtrim($path, '/');
		$this->hostName = gethostname();
	}

	public function load() {
		$envFileName = $this->getEnvFileByHostName();
		putenv('ENV_NAME = ' . $envFileName);
		$_ENV['ENV_NAME'] = $envFileName;

		$dotEnv = Dotenv::create($this->envPath, $envFileName);
		$dotEnv->load();

	}

	private function getEnvFileByHostName($hostname = '') {
		if (empty($hostname)) {
			$hostname = $this->hostName;
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
}