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

use Dotenv\Exception\InvalidFileException;
use Dotenv\Parser;
use Dotenv\Regex\Regex;
use PhpOption\Option;

class Loader extends \Dotenv\Loader {
	private static $operateKey = [
		'|',
		'&',
		'^'
	];

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

	protected function splitStringIntoParts($line) {
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

	public function loadDirect($content) {
		return $this->processEntries(
			Lines::process(preg_split("/(\r\n|\n|\r)/", $content))
		);
	}

	protected function processEntries(array $entries) {
		$vars = [];

		foreach ($entries as $entry) {
			//判断是否是include(env),如果是，先加载该env
			if ($this->checkAndLoadIncludeEnv($entry)) {
				continue;
			}

			list($name, $value) = $this->splitStringIntoParts($entry);
			$entry = $name . ' = ' . self::parseValue($value);

			list($name, $value) = Parser::parse($entry);
			$vars[$name] = $this->resolveNestedVariables($value);
			$this->setEnvironmentVariable($name, $vars[$name]);
		}

		return $vars;
	}

	protected function checkAndLoadIncludeEnv($entry) {
		$pattern = '/^include\(([\.\w]+)\)/';
		if (preg_match($pattern, $entry, $result) && !empty($result[1])) {
			$paths = [];
			foreach ($this->filePaths as $filePath) {
				$paths[] = dirname($filePath) . '/' . $result[1];
			}
			$this->filePaths = array_unique($paths);
			$this->load();

			return true;
		}

		return false;
	}

	protected function resolveNestedVariables($value = null) {
		return Option::fromValue($value)
			->filter(function ($str) {
				return strpos($str, '$') !== false;
			})
			->flatMap(function ($str) {
				return Regex::replaceCallback(
					'/\${([a-zA-Z0-9_.]+)}/',
					function (array $matches) {
						return Option::fromValue($this->getEnvironmentVariable($matches[1]))
							->getOrElse($matches[0]);
					},
					$str
				)->success();
			})
			->getOrElse($value);
	}
}
