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

namespace W7\Core\Config;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Lines;
use PhpOption\Option;

class Env {
	private $envPath = '';

	private $hostName = '';

	private $defaultName = '.env';

	private static $operateKey = [
		'|',
		'&',
		'^'
	];

	public function __construct($path) {
		if (empty($path) || !is_dir($path)) {
			throw new \RuntimeException('Invalid env path');
		}
		$this->envPath = rtrim($path, '/');
		$this->hostName = gethostname();
	}

	public function load() {
		//加载当前环境的.env，覆盖默认的.env数据
		$envName = getenv('ENV_NAME') ?: 'default';

		$envFileName = $this->getEnvFileByHostName($envName);
		if (!empty($envFileName) && file_exists($this->envPath . '/' . $envFileName)) {
			putenv('ENV_NAME=' . $envFileName);
			$_ENV['ENV_NAME'] = $envFileName;
			$path = $this->preProcess($envFileName);
			$dotEnv = Dotenv::create($this->envPath, $path ? $path : $envFileName);
			$dotEnv->overload();
			if ($path) {
				unlink($this->envPath . $path);
			}
		}
	}

	//采用处理后写到临时文件的方式
	private function preProcess($path) {
		$content = file_get_contents($path);
		$lines = Option::fromValue($content, false);
		if ($lines->isDefined()) {
			$lines = $lines->get();
			$entries = Lines::process(preg_split("/(\r\n|\n|\r)/", $lines));
			foreach ($entries as &$entry) {
				list($name, $value) = $this->splitStringIntoParts($entry);
				$entry = $name . ' = ' . self::parseValue($value);
			}
			$entries = implode("\r\n", $entries);
			$fileName = '/tmp_env';
			file_put_contents($this->envPath . $fileName, $entries);
			return $fileName;
		}
	}

	public static function parseValue($value) {
		if (preg_match('/^.*[\|\^\&]+.*$/', $value)) {
			foreach (self::$operateKey as $key) {
				$value = explode($key, $value);
				$value = array_map(function ($value) {
					return \trim($value);
				}, $value);
				$value = implode($key, $value);
			}
		}

		return $value;
	}

	private function splitStringIntoParts($line) {
		$name = $line;
		$value = null;

		if (strpos($line, '=') !== false) {
			list($name, $value) = array_map('trim', explode('=', $line, 2));
		}

		if ($name === '') {
			throw new InvalidFileException(
				sprintf(
					'Failed to parse dotenv file due to %s. Failed at [%s].',
					'an unexpected equals',
					strtok($line, "\n")
				)
			);
		}

		return [$name, $value];
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
}
