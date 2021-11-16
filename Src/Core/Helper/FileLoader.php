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

namespace W7\Core\Helper;

class FileLoader {
	protected array $ignoreFiles;
	protected array $ignoreRules;
	protected string $rootDir;

	public function __construct($rootDir, array $ignoreRules = []) {
		$this->rootDir = $rootDir;
		$this->ignoreRules = $ignoreRules;
	}

	public function getIgnoreFiles(): ?array {
		if (isset($this->ignoreFiles)) {
			return $this->ignoreFiles;
		}

		$rootDir = $this->rootDir;
		$matches = array();
		foreach ($this->ignoreRules as $ignoreRule) {
			$ignoreRule = trim($ignoreRule);
			if ($ignoreRule === '') {
				continue;
			}
			if ($ignoreRule[0] === '#') {
				continue;
			}
			if ($ignoreRule[0] === '!') {
				$ignoreRule = substr($ignoreRule, 1);

				$parentDir = dirname($ignoreRule);
				$parentLoadDir = $rootDir;
				if ($parentDir !== '.' && $parentDir !== '..') {
					$parentLoadDir .= '/' . $parentDir;
				}
				$files = array_diff(glob("$parentLoadDir/*"), glob("$rootDir/$ignoreRule"));
			} else {
				$files = glob("$rootDir/$ignoreRule");
			}
			$matches = array_merge($matches, (array)$files);
		}

		return $this->ignoreFiles = $matches;
	}

	public function isIgnoreFile($file): bool {
		$ignoreFiles = $this->getIgnoreFiles();
		foreach ($ignoreFiles as $ignoreFile) {
			if ($file === $ignoreFile || str_starts_with($file, $ignoreFile)) {
				return true;
			}
		}

		return false;
	}

	public function loadFile($file) {
		if ($this->isIgnoreFile($file)) {
			return null;
		}

		return include $file;
	}
}
